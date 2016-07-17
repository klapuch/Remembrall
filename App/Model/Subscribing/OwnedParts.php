<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;
use Remembrall\Exception;
use Remembrall\Model\{
	Access, Http, Storage
};

/**
 * Parts which are owned by the given subscriber
 */
final class OwnedParts implements Parts {
	private $origin;
	private $database;
	private $myself;
	private $browser;

	public function __construct(
		Parts $origin,
		Dibi\Connection $database,
		Access\Subscriber $myself,
		Http\Browser $browser
	) {
		$this->origin = $origin;
		$this->database = $database;
		$this->myself = $myself;
		$this->browser = $browser;
	}

	public function subscribe(
		Part $part,
		string $url,
		string $expression,
		Interval $interval
	): Part {
		try {
			(new Storage\Transaction($this->database))->start(
				function() use ($part, $url, $expression, $interval) {
					$this->origin->subscribe(
						$part,
						$url,
						$expression,
						$interval
					);
					$this->database->query(
						'INSERT INTO subscribed_parts
						(part_id, subscriber_id, `interval`) VALUES
						((SELECT ID FROM parts WHERE expression = ? AND page_url = ?), ?, ?)',
						$expression,
						$url,
						$this->myself->id(),
						sprintf('PT%dM', $interval->step()->i)
					);
				}
			);
			return $part;
		} catch(Dibi\UniqueConstraintViolationException $ex) {
			throw new Exception\DuplicateException(
				sprintf(
					'"%s" expression on the "%s" page is already subscribed by you',
					$expression,
					$url
				),
				$ex->getCode(),
				$ex
			);
		}
	}

	public function remove(string $url, string $expression) {
		if(!$this->owned($url, $expression))
			throw new Exception\NotFoundException('You do not own this part');
		$this->database->query(
			'DELETE FROM subscribed_parts
			WHERE subscriber_id = ?
			AND part_id = (SELECT ID FROM parts WHERE expression = ? AND page_url = ?)',
			$this->myself->id(),
			$expression,
			$url
		);
	}

	public function iterate(): array {
		return (array)array_reduce(
			$this->database->fetchAll(
				'SELECT parts.content AS part_content, expression, url,
				pages.content AS page_content, `interval`, (
					SELECT visited_at
					FROM part_visits
					WHERE part_id = parts.ID
					ORDER BY visited_at DESC
					LIMIT 1
				) AS visited_at
				FROM parts
				INNER JOIN subscribed_parts ON subscribed_parts.part_id = parts.ID  
				LEFT JOIN pages ON pages.url = parts.page_url
				WHERE subscriber_id = ?',
				$this->myself->id()
			),
			function($previous, Dibi\Row $row) {
				$previous[] = new ConstantPart(
					new HtmlPart(
						new XPathExpression(
							new ConstantPage(
								$row['page_content'],
								$row['url']
							),
							$row['expression']
						),
						$this->browser,
						new ConstantPage(
							$row['page_content'],
							$row['url']
						)
					),
					$row['part_content'],
					new ConstantPage(
						$row['page_content'],
						$row['url']
					),
					new DateTimeInterval(
						new \DateTimeImmutable((string)$row['visited_at']),
						new \DateInterval($row['interval'])
					)
				);
				return $previous;
			}
		);
	}

	/**
	 * Checks whether the subscriber really owns the given part
	 * @param string $url
	 * @param string $expression
	 * @return bool
	 */
	private function owned(string $url, string $expression): bool {
		return (bool)$this->database->fetchSingle(
			'SELECT 1
			FROM parts
			INNER JOIN subscribed_parts ON subscribed_parts.part_id = parts.ID
			WHERE subscriber_id = ? AND page_url = ? AND expression = ?',
			$this->myself->id(),
			$url,
			$expression
		);
	}
}
