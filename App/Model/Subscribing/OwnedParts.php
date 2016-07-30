<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;
use Remembrall\Exception;
use Remembrall\Model\{
	Access, Storage
};

/**
 * Parts which are owned with the given subscriber
 */
final class OwnedParts implements Parts {
	private $origin;
	private $database;
	private $myself;

	public function __construct(
		Parts $origin,
		Dibi\Connection $database,
		Access\Subscriber $myself
	) {
		$this->origin = $origin;
		$this->database = $database;
		$this->myself = $myself;
	}

	public function add(Part $part, string $url, string $expression): Part {
		return $this->origin->add($part, $url, $expression);
	}

	public function iterate(): array {
		return (array)array_reduce(
			$this->database->fetchAll(
				'SELECT parts.content AS part_content, expression, url,
				pages.content AS page_content, interval, (
					SELECT MAX(visited_at)
					FROM part_visits
					WHERE part_id = parts.id
				) AS visited_at
				FROM parts
				INNER JOIN subscriptions ON subscriptions.part_id = parts.id  
				LEFT JOIN pages ON pages.url = parts.page_url
				WHERE subscriber_id = ?
				ORDER BY visited_at DESC',
				$this->myself->id()
			),
			function($previous, Dibi\Row $row) {
				$previous[] = new ConstantPart(
					new HtmlPart(
						new XPathExpression(
							new ConstantPage($row['page_content']),
							$row['expression']
						),
						new ConstantPage($row['page_content'])
					),
					$row['part_content'],
					$row['url']
				);
				return $previous;
			}
		);
	}
}
