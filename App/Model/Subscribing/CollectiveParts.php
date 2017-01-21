<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Http, Storage, Uri, Output
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
		foreach($this->rows() as $part) {
			$page = new StoredPage(
				new HtmlWebPage(
					new Http\BasicRequest(
						'GET',
						new Uri\ReachableUrl(new Uri\ValidUrl($part['url']))
					)
				),
				new Uri\ValidUrl($part['url']),
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

	public function print(Output\Format $format): array {
		return array_map(
			function(array $part) use ($format): Output\Format {
				return $format->with('id', $part['id'])
					->with('url', $part['url'])
					->with('expression', $part['expression'])
					->with('content', $part['content']);
			},
			$this->rows()
		);
	}

	private function rows(): array {
		return (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT id, page_url AS url, snapshot, content, expression FROM parts'
		))->rows();
	}
}