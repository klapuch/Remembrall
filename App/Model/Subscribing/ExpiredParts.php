<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Storage, Uri, Http
};

/**
 * All the parts which are needed to visit because they are no more valid
 */
final class ExpiredParts implements Parts {
	private $origin;
	private $database;

	public function __construct(Parts $origin, Storage\Database $database ) {
		$this->origin = $origin;
		$this->database = $database;
	}

	public function add(Part $part, Uri\Uri $uri, string $expression): Part {
		return $this->origin->add($part, $uri, $expression);
	}

	public function iterate(): array {
		return (array)array_reduce(
			$this->database->fetchAll(
				"SELECT parts.content AS part_content, expression,
				pages.content AS page_content, url 
				FROM parts
				LEFT JOIN (
					SELECT MIN(SUBSTRING(interval FROM '[0-9]+')::INT) AS interval,
					part_id, MIN(last_update) AS last_update
					FROM subscriptions
					GROUP BY part_id
				) AS subscriptions ON subscriptions.part_id = parts.id 
				INNER JOIN pages ON pages.url = parts.page_url
				WHERE last_update + INTERVAL '1 MINUTE' * INTERVAL < NOW()
				ORDER BY last_update ASC"
			),
			function($previous, array $row) {
				$previous[] = new PostgresPart(
					new HtmlPart(
						new XPathExpression(
							new HtmlWebPage(
								new Http\BasicRequest(
									'GET',
									new Uri\ValidUrl($row['url'])
								)
							),
							$row['expression']
						),
						new ConstantPage(
							new HtmlWebPage(
								new Http\BasicRequest(
									'GET',
									new Uri\ValidUrl($row['url'])
								)
							),
							$row['page_content']
						)
					), new Uri\ValidUrl($row['url']),
					$row['expression'],
					$this->database
				);
				return $previous;
			}
		);
	}
}
