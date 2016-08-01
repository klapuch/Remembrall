<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;

/**
 * All the parts which are needed to visit because they are no more valid
 */
final class ExpiredParts implements Parts {
	private $origin;
	private $database;

	public function __construct(Parts $origin, Dibi\Connection $database) {
		$this->origin = $origin;
		$this->database = $database;
	}

	public function add(Part $part, string $url, string $expression): Part {
		return $this->origin->add($part, $url, $expression);
	}

	public function iterate(): array {
		return (array)array_reduce(
			$this->database->fetchAll(
				'SELECT parts.content AS part_content, expression,
				pages.content AS page_content, url 
				FROM parts
				LEFT JOIN (
					SELECT MIN(CAST(SUBSTRING(interval FROM "[0-9]+") AS INT)) AS interval,
					part_id, MIN(last_update) AS last_update
					FROM subscriptions
					GROUP BY part_id
				) AS subscriptions ON subscriptions.part_id = parts.id 
				INNER JOIN pages ON pages.url = parts.page_url
				WHERE last_update + INTERVAL "1 MINUTE" * interval < NOW()
				ORDER BY last_update ASC'
			),
			function($previous, Dibi\Row $row) {
				$previous[] = new ConstantPart(
					new HtmlPart(
						new XPathExpression(
							new ConstantPage(
								new FakePage(),
								$row['page_content']
							),
							$row['expression']
						),
						new ConstantPage(new FakePage(), $row['page_content'])
					),
					$row['part_content'],
					$row['url']
				);
				return $previous;
			}
		);
	}
}
