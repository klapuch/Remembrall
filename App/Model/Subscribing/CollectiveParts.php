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

	public function add(Part $part, string $url, string $expression): Part {
		(new Storage\Transaction($this->database))->start(
			function() use ($part, $url, $expression) {
				if($this->alreadyExists($url, $expression)) {
					$this->database->query(
						'UPDATE parts
						SET content = ?
						WHERE page_url = ? AND expression = ?',
						$part->content(),
						$url,
						$expression
					);
				} else {
					$this->database->query(
						'INSERT INTO parts
						(page_url, expression, content) VALUES
						(?, ?, ?)',
						$url,
						$expression,
						$part->content()
					);
				}
				$this->database->query(
					'INSERT INTO part_visits (part_id, visited_at) VALUES
					((SELECT id FROM parts WHERE page_url = ? AND expression = ?), ?)',
					$url,
					$expression,
					new \DateTimeImmutable()
				);
			}
		);
		return $part;
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

	/**
	 * Does the part already exists?
	 * @param string $url
	 * @param string $expression
	 * @return bool
	 */
	private function alreadyExists(string $url, string $expression): bool {
		return (bool)$this->database->fetchSingle(
			'SELECT 1 FROM parts WHERE page_url = ? AND expression = ?',
			$url,
			$expression
		);
	}
}
