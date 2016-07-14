<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;
use Remembrall\Model\Access;

/**
 * Owner's reports
 */
final class OwnedReports implements Reports {
	private $owner;
	private $database;

	public function __construct(
		Access\Subscriber $owner,
		Dibi\Connection $database
	) {
		$this->owner = $owner;
		$this->database = $database;
	}

	public function iterate(): array {
		return (array)array_reduce(
			$this->database->fetchAll(
				'SELECT reports.ID AS report_id, sent_at,
				pages.content AS page_content, url,
				parts.content AS part_content, expression, `interval`,
				part_visits.visited_at
				FROM parts
				INNER JOIN subscribed_parts ON subscribed_parts.part_id = parts.ID 
				LEFT JOIN reports ON parts.ID = reports.part_id
				LEFT JOIN part_visits ON part_visits.part_id = parts.ID
				INNER JOIN pages ON pages.ID = parts.page_id
				WHERE subscribed_parts.subscriber_id = ?',
				$this->owner->id()
			),
			function($previous, Dibi\Row $row) {
				array_unshift(
					$previous,
					new ConstantReport(
						$this->owner,
						new ConstantPart(
							new ConstantPage(
								$row['url'],
								$row['page_content']
							),
							$row['part_content'],
							new XPathExpression(
								new ConstantPage(
									$row['url'],
									$row['page_content']
								),
								$row['expression']
							),
							new DateTimeInterval(
								new \DateTimeImmutable(
									(string)$row['visited_at']
								),
								new \DateInterval($row['interval'])
							)
						),
						new \DateTimeImmutable((string)$row['sent_at'])
					)
				);
				return $previous;
			},
			[]
		);
	}

	public function archive(Part $part) {
		$this->database->query(
			'INSERT INTO reports (part_id, sent_at) VALUES
			((SELECT parts.ID
				FROM parts
				INNER JOIN subscribed_parts ON subscribed_parts.part_id = parts.ID 
				WHERE subscriber_id = ?
				AND expression = ?
				AND page_id = (SELECT ID FROM pages WHERE url = ?)
			), ?)',
			$this->owner->id(),
			(string)$part->expression(),
			$part->source()->url(),
			new \DateTimeImmutable()
		);
	}
}