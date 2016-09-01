<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Storage, Uri
};

/**
 * All parts stored in the database shared with everyone
 */
final class CollectiveParts implements Parts {
	private $database;

	public function __construct(Storage\Database $database) {
		$this->database = $database;
	}

	public function add(Part $part, Uri\Uri $uri, string $expression): Part {
        if(!$this->alreadyExists($uri, $expression)) {
            $this->database->query(
                'INSERT INTO parts
                (page_url, expression, content) VALUES
                (?, ?, ?)',
                [$uri->reference(), $expression, $part->content()]
            );
        }
        return $part;
	}

	public function iterate(): array {
		return (array)array_reduce(
			$this->database->fetchAll(
				'SELECT parts.content AS part_content, url,
				pages.content AS page_content, expression,
				interval
				FROM parts
				INNER JOIN subscriptions ON subscriptions.part_id = parts.id
				LEFT JOIN pages ON pages.url = parts.page_url'
			),
			function($previous, array $row) {
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
	 * @param Uri\Uri $uri
	 * @param string $expression
	 * @return bool
	 */
	private function alreadyExists(Uri\Uri $uri, string $expression): bool {
		return (bool)$this->database->fetchColumn(
			'SELECT 1
			FROM parts
			WHERE page_url IS NOT DISTINCT FROM ?
			AND expression IS NOT DISTINCT FROM ?',
			[$uri->reference(), $expression]
		);
	}
}
