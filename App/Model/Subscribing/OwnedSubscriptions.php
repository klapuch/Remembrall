<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;
use Remembrall\Model\Access;
use Remembrall\Exception;

final class OwnedSubscriptions implements Subscriptions {
	private $owner;
	private $database;

	public function __construct(
		Access\Subscriber $owner,
		Dibi\Connection $database
	) {
		$this->owner = $owner;
		$this->database = $database;
	}

	public function iterate(): array {
		return (array)array_reduce(
			$this->database->fetchAll(
				'SELECT expression, page_url AS url
				FROM parts
				INNER JOIN subscriptions ON subscriptions.part_id = parts.id
				WHERE subscriptions.subscriber_id = ?',
				$this->owner->id()
			),
			function($subscriptions, Dibi\Row $row) {
				$subscriptions[] = new OwnedSubscription(
					$row['url'],
					$row['expression'],
					$this->owner,
					$this->database
				);
				return $subscriptions;
			}
		);
	}

	public function subscribe(
		string $url,
		string $expression,
		Interval $interval
	) {
		try {
			$this->database->query(
				'INSERT INTO subscriptions
				(part_id, subscriber_id, interval) VALUES
				((SELECT id FROM parts WHERE expression = ? AND page_url = ?), ?, ?)',
				$expression,
				$url,
				$this->owner->id(),
				sprintf('PT%dM', $interval->step()->i)
			);
		} catch(Dibi\UniqueConstraintViolationException $ex) {
			throw new Exception\DuplicateException(
				sprintf(
					'"%s" expression on the "%s" page is already subscribed by you',
					$expression,
					$url
				),
				(int)$ex->getCode(),
				$ex
			);
		}
	}
}