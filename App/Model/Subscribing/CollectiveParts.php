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

	public function __construct(\PDO $database) {
		$this->database = $database;
	}

	public function add(Part $part, Uri\Uri $url, string $expression): void {
		(new Storage\ParameterizedQuery(
			$this->database,
			'INSERT INTO parts (page_url, expression, content, snapshot) VALUES
			(:url, :expression, :content, :snapshot)
			ON CONFLICT (page_url, expression)
			DO UPDATE SET content = :content, snapshot = :snapshot',
			[
				'url' => $url->reference(),
				'expression' => $expression,
				'content' => $part->content(),
				'snapshot' => $part->snapshot(),
			]
		))->execute();
	}

	public function getIterator(): \Iterator {
		$parts = (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT id, page_url, snapshot, content, expression FROM parts'
		))->rows();
		foreach($parts as $part) {
			$page = new StoredPage(
				new HtmlWebPage(
					new Http\BasicRequest(
						'GET',
						new Uri\ReachableUrl(new Uri\ValidUrl($part['page_url']))
					)
				),
				new Uri\ValidUrl($part['page_url']),
				$this->database
			);
			yield new ConstantPart(
				new StoredPart(
					new HtmlPart(
						new MatchingExpression(
							new XPathExpression($page, $part['expression'])
						),
						$page
					),
					$part['id'],
					$this->database
				),
				$part['content'],
				$part['snapshot']
			);
		}
	}
}