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

	public function subscribe(
		Part $part,
		string $url,
		string $expression,
		Interval $interval
	): Part {
		(new Storage\Transaction($this->database))->start(
			function() use ($part, $url, $expression, $interval) {
				$this->database->query(
					'INSERT INTO parts
					(page_url, expression, content) VALUES (?, ?, ?)
					ON DUPLICATE KEY UPDATE content = VALUES(content)',
					$url,
					$expression,
					$part->content()
				);
				$this->database->query(// TODO
					'INSERT INTO part_visits (part_id, visited_at) VALUES
					((SELECT ID FROM parts WHERE page_url = ? AND expression = ?), ?)',
					$url,
					$expression,
					$interval->start()
				);
			}
		);
		return $part;
	}

	public function remove(string $url, string $expression) {
		$this->database->query(
			'DELETE FROM parts WHERE expression = ? AND page_url = ?',
			$expression,
			$url
		);
	}

	public function iterate(): array {
		return (array)array_reduce(
			$this->database->fetchAll(
				'SELECT parts.content AS part_content, url,
				pages.content AS page_content, expression,
				`interval`, (
					SELECT visited_at
					FROM part_visits
					WHERE part_id = parts.ID
					ORDER BY visited_at DESC
					LIMIT 1
				) AS visited_at
				FROM parts
				INNER JOIN subscribed_parts ON subscribed_parts.part_id = parts.ID
				LEFT JOIN pages ON pages.url = parts.page_url'
			),
			function($previous, Dibi\Row $row) {
				$previous[] = new ConstantPart(
					new HtmlPart(
						new XPathExpression(
							new ConstantPage(
								$row['page_content'],
								$row['url']
							),
							$row['expression']
						)
					),
					$row['part_content'],
					new ConstantPage(
						$row['page_content'],
						$row['url']
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
