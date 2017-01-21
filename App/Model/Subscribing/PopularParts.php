<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Http, Storage, Uri, Output
};

/**
 * The most subscribed and therefore the most popular parts
 */
final class PopularParts implements Parts {
	private $origin;
	private $database;

	public function __construct(Parts $origin, \PDO $database) {
		$this->origin = $origin;
		$this->database = $database;
	}

	public function add(Part $part, Uri\Uri $url, string $expression): void {
		$this->origin->add($part, $url, $expression);
	}

	// TODO: Not definite
	public function getIterator(): \Iterator {
		foreach($this->rows() as $part) {
			$page = new StoredPage(
				new HtmlWebPage(
					new Http\BasicRequest(
						'GET',
						new Uri\ValidUrl($part['url'])
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
					->with('content', $part['content'])
					->with('occurrences', $part['occurrences']);
			},
			$this->rows()
		);
	}

	private function rows(): array {
		return (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT id, page_url AS url, expression, content, snapshot, occurrences
			FROM parts
			INNER JOIN (
				SELECT part_id, COUNT(*) AS occurrences
				FROM subscriptions
				GROUP BY part_id
				ORDER BY occurrences DESC
			) AS subscriptions ON subscriptions.part_id = parts.id'
		))->rows();
	}
}