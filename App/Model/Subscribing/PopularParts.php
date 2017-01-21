<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Http, Storage, Uri
};

/**
 * The most subscribed and therefore the most popular parts
 */
final class PopularParts extends Parts {
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

	protected function rows(): array {
		return (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT id, page_url AS url, expression, content, snapshot
			FROM parts
			INNER JOIN (
				SELECT part_id, COUNT(*) AS popularity
				FROM subscriptions
				GROUP BY part_id
				ORDER BY popularity DESC
			) AS subscriptions ON subscriptions.part_id = parts.id'
		))->rows();
	}
}