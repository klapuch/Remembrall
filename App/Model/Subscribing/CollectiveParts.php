<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Http, Storage, Uri
};

/**
 * All parts stored in the database shared with everyone
 */
final class CollectiveParts implements Parts {
	private $database;

	public function __construct(Storage\Database $database) {
		$this->database = $database;
	}

	public function add(Part $part, Uri\Uri $url, string $expression): void {
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content, snapshot) VALUES
			(:url, :expression, :content, :snapshot)
			ON CONFLICT (page_url, expression)
			DO UPDATE SET content = :content, snapshot = :snapshot',
			[
				':url' => $url->reference(),
				':expression' => $expression,
				':content' => $part->content(),
				':snapshot' => $part->snapshot(),
			]
		);
	}

	public function iterate(): \Iterator {
		$rows = $this->database->fetchAll(
			'SELECT page_url AS url, snapshot, content, id, expression
			FROM parts'
		);
		foreach($rows as $row) {
			$url = new Uri\ReachableUrl(new Uri\ValidUrl($row['url']));
			$page = new FrugalPage(
				$url,
				new PostgresPage(
					new HtmlWebPage(new Http\BasicRequest('GET', $url)),
					$url,
					$this->database
				),
				$this->database
			);
			yield new ConstantPart(
				new PostgresPart(
					new HtmlPart(
						new MatchingExpression(
							new XPathExpression($page, $row['expression'])
						),
						$page
					),
					$row['id'],
					$this->database
				),
				$row['content'],
				$row['snapshot']
			);
		}
	}
}