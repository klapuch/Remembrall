<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;
use Remembrall\Model\{
	Storage
};

/**
 * All parts stored in the database shared with everyone
 */
final class CollectiveParts implements Parts {
	private $database;

	public function __construct(Dibi\Connection $database) {
		$this->database = $database;
	}

	public function subscribe(Part $part, Interval $interval): Part {
		(new Storage\Transaction($this->database))->start(
			function() use ($part, $interval) {
				$this->database->query(
					'INSERT INTO parts
					(page_url, expression, content) VALUES
					(?, ?, ?)
					ON DUPLICATE KEY UPDATE content = VALUES(content)',
					$part->source()->url(),
					(string)$part->expression(),
					$part->content()
				);
				$partId = $this->database->insertId();
				$this->database->query(
					'INSERT INTO part_visits (part_id, visited_at) VALUES
					(?, ?)',
					$partId,
					$interval->start()
				);
			}
		);
		return $part;
	}

	public function replace(Part $old, Part $new): Part {
		$this->database->query(
			'UPDATE parts
			INNER JOIN subscribed_parts ON subscribed_parts.part_id = parts.ID
			INNER JOIN part_visits ON part_visits.part_id = parts.ID
			SET content = ?, visited_at = NOW()
			WHERE expression = ?
			AND page_url = ?',
			$new->content(),
			(string)$old->expression(),
			$old->source()->url()
		);
		return $new;
	}

	public function remove(Part $part) {
		$this->database->query(
			'DELETE FROM parts
			WHERE expression = ? AND page_url = ?',
			(string)$part->expression(),
			$part->source()->url()
		);
	}

	public function iterate(): array {
		return (array)array_reduce(
			$this->database->fetchAll(
				'SELECT parts.content AS part_content, url,
				pages.content AS page_content, expression,
				`interval`, visited_at
				FROM parts
				INNER JOIN subscribed_parts ON subscribed_parts.part_id = parts.ID
				INNER JOIN part_visits ON part_visits.part_id = parts.ID 
				LEFT JOIN pages ON pages.url = parts.page_url'
			),
			function($previous, Dibi\Row $row) {
				$previous[] = new ConstantPart(
					new ConstantPage($row['url'], $row['page_content']),
					$row['part_content'],
					new XPathExpression(
						new ConstantPage($row['url'], $row['page_content']),
						$row['expression']
					),
					new DateTimeInterval(
						new \DateTimeImmutable((string)$row['visited_at']),
						new \DateInterval($row['interval'])
					)
				);
				return $previous;
			}
		);
	}
}
