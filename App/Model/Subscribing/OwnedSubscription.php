<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Dibi;
use Remembrall\Exception;
use Remembrall\Model\Access;

final class OwnedSubscription implements Subscription {
	private $url;
	private $expression;
	private $database;
	private $owner;

	public function __construct(
		string $url,
		string $expression,
		Access\Subscriber $owner,
		Dibi\Connection $database
	) {
		$this->url = $url;
		$this->expression = $expression;
		$this->owner = $owner;
		$this->database = $database;
	}

	public function cancel() {
		if(!$this->owned())
			throw new Exception\NotFoundException('You do not own this part');
		$this->database->query(
			'DELETE FROM subscriptions
			WHERE subscriber_id = ?
			AND part_id = (
				SELECT id
				FROM parts
				WHERE expression = ?
				AND page_url = ?
			)',
			$this->owner->id(),
			$this->expression,
			$this->url
		);
	}

	public function edit(Interval $interval): Subscription {
		if(!$this->owned())
			throw new Exception\NotFoundException('You do not own this part');
		$this->database->query(
			'UPDATE subscriptions
			SET interval = ?
			WHERE subscriber_id IS NOT DISTINCT FROM ?
			AND part_id IS NOT DISTINCT FROM (
				SELECT ID
				FROM parts
				WHERE page_url IS NOT DISTINCT FROM ?
				AND expression IS NOT DISTINCT FROM ?
			)',
			sprintf('PT%dM', $interval->step()->i),
			$this->owner->id(),
			$this->url,
			$this->expression
		);
		return $this;
	}

	public function print(): array {
		return [
			'url' => $this->url,
			'expression' => $this->expression,
			'subscriber' => $this->owner,
		];
	}

	/**
	 * Checks whether the subscriber really owns the part
	 * @return bool
	 */
	private function owned(): bool {
		return (bool)$this->database->fetchSingle(
			'SELECT 1
			FROM parts
			INNER JOIN subscriptions ON subscriptions.part_id = parts.id
			WHERE subscriber_id IS NOT DISTINCT FROM ?
			AND page_url IS NOT DISTINCT FROM ?
			AND expression IS NOT DISTINCT FROM ?',
			$this->owner->id(),
			$this->url,
			$this->expression
		);
	}
}