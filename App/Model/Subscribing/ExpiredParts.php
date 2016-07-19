<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;

/**
 * Expired parts
 */
final class ExpiredParts implements Parts {
	private $origin;
	private $database;

	public function __construct(Parts $origin, Dibi\Connection $database) {
		$this->origin = $origin;
		$this->database = $database;
	}

	public function subscribe(
		Part $part,
		string $url,
		string $expression,
		Interval $interval
	): Part {
		return $this->origin->subscribe($part, $url, $expression, $interval);
	}

	public function remove(string $url, string $expression) {
		$this->origin->remove($url, $expression);
	}

	public function iterate(): array {
		return (array)array_reduce(
			$this->database->fetchAll(
				'SELECT parts.content AS part_content, expression,
				`interval`, visited_at,
				pages.content AS page_content, url 
				FROM parts
				LEFT JOIN subscribed_parts ON subscribed_parts.part_id = parts.ID 
				INNER JOIN pages ON pages.url = parts.page_url
				LEFT JOIN part_visits ON part_visits.part_id = parts.ID
				WHERE visited_at IS NULL
				OR visited_at + INTERVAL CAST(SUBSTR(`interval`, 3) AS UNSIGNED) MINUTE <= NOW()
				GROUP BY parts.ID'
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
						),
						new ConstantPage(
							$row['page_content'],
							$row['url']
						)
					),
					$row['part_content'],
					$row['url'],
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
