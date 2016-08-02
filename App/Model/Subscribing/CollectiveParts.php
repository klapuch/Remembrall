<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;
use Remembrall\Model\Storage;

/**
 * All parts stored in the database shared with everyone
 */
final class CollectiveParts implements Parts {
	private $database;

	public function __construct(Dibi\Connection $database) {
		$this->database = $database;
	}

	public function add(Part $part, string $url, string $expression): Part {
		return (new Storage\Transaction($this->database))->start(
			function() use ($part, $url, $expression) {
				if($this->alreadyExists($url, $expression)) {
					return $part->refresh();
				} else {
					$this->database->query(
						'INSERT INTO parts
						(page_url, expression, content) VALUES
						(?, ?, ?)',
						$url,
						$expression,
						$part->content()
					);
					return $part;
				}
			}
		);
	}

	public function iterate(): array {
		return (array)array_reduce(
			$this->database->fetchAll(
				'SELECT parts.content AS part_content, url,
				pages.content AS page_content, expression,
				interval, (
					SELECT MAX(visited_at)
					FROM part_visits
					WHERE part_id = parts.id
				) AS visited_at
				FROM parts
				INNER JOIN subscriptions ON subscriptions.part_id = parts.id
				LEFT JOIN pages ON pages.url = parts.page_url'
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

	/**
	 * Does the part already exists?
	 * @param string $url
	 * @param string $expression
	 * @return bool
	 */
	private function alreadyExists(string $url, string $expression): bool {
		return (bool)$this->database->fetchSingle(
			'SELECT 1
			FROM parts
			WHERE page_url IS NOT DISTINCT FROM ?
			AND expression IS NOT DISTINCT FROM ?',
			$url,
			$expression
		);
	}
}
