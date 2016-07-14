<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;
use Remembrall\Exception;
use Remembrall\Model\{
	Access, Storage
};

/**
 * Parts which are owned by the given subscriber
 */
final class OwnedParts implements Parts {
	private $origin;
	private $database;
	private $myself;

	public function __construct(
		Parts $origin,
		Dibi\Connection $database,
		Access\Subscriber $myself
	) {
		$this->origin = $origin;
		$this->database = $database;
		$this->myself = $myself;
	}

	public function subscribe(Part $part, Interval $interval): Part {
		try {
			(new Storage\Transaction($this->database))->start(
				function() use ($interval, $part) {
					$this->origin->subscribe($part, $interval);
					$this->database->query(
						'INSERT INTO subscribed_parts
						(part_id, subscriber_id, `interval`) VALUES
						((SELECT ID FROM parts WHERE expression = ? AND page_url = ?), ?, ?)',
						(string)$part->expression(),
						$part->source()->url(),
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
					(string)$part->expression(),
					$part->source()->url()
				),
				$ex->getCode(),
				$ex
			);
		}
	}

	public function replace(Part $old, Part $new): Part {
		if(!$this->owned($old))
			throw new Exception\NotFoundException('You do not own this part');
		return $this->origin->replace($old, $new);
	}

	public function remove(Part $part) {
		if(!$this->owned($part))
			throw new Exception\NotFoundException('You do not own this part');
		$this->database->query(
			'DELETE FROM subscribed_parts
			WHERE subscriber_id = ?
			AND part_id = (SELECT ID FROM parts WHERE expression = ? AND page_url = ?)',
			$this->myself->id(),
			(string)$part->expression(),
			$part->source()->url()
		);
	}

	public function iterate(): array {
		return (array)array_reduce(
			$this->database->fetchAll(
				'SELECT parts.content AS part_content, expression, url,
				pages.content AS page_content, `interval`, visited_at
				FROM parts
				INNER JOIN subscribed_parts ON subscribed_parts.part_id = parts.ID
				INNER JOIN part_visits ON part_visits.part_id = parts.ID  
				LEFT JOIN pages ON pages.url = parts.page_url
				WHERE subscriber_id = ?',
				$this->myself->id()
			),
			function($previous, Dibi\Row $row) {
				$previous[] = new ConstantPart(
					new ConstantPage($row['url'], $row['page_content']),
					$row['part_content'],
					new XPathExpression(
						new ConstantPage($row['url'], $row['page_content']),
						$row['expression']
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
	 * @param Part $part
	 * @return bool
	 */
	private function owned(Part $part): bool {
		return (bool)array_filter(
			$this->iterate(),
			function(Part $ownedPart) use ($part) {
				return $ownedPart->equals($part);
			}
		);
	}
}
