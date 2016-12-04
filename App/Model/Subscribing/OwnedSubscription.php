<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Storage, Time
};
use Remembrall\Exception\NotFoundException;
use Remembrall\Model\Access;

/**
 * Subscription owned by one particular subscriber
 */
final class OwnedSubscription implements Subscription {
	private $origin;
	private $id;
	private $owner;
	private $database;

	public function __construct(
		Subscription $origin,
		int $id,
		Access\Subscriber $owner,
		Storage\Database $database
	) {
		$this->origin = $origin;
		$this->id = $id;
		$this->owner = $owner;
		$this->database = $database;
	}

	public function cancel(): void {
		if(!$this->owned()) {
			throw new NotFoundException(
				'You can not cancel foreign subscription'
			);
		}
		$this->origin->cancel();
	}

	public function edit(Time\Interval $interval): void {
		if(!$this->owned()) {
			throw new NotFoundException(
				'You can not edit foreign subscription'
			);
		}
		$this->origin->edit($interval);
	}

	public function notify(): void {
		if(!$this->owned()) {
			throw new NotFoundException(
				'You can not be notified on foreign subscription'
			);
		}
		$this->origin->notify();
	}

	/**
	 * Is the current subscriber owner of the subscription?
	 * @return bool
	 */
	private function owned(): bool {
		return (bool)$this->database->fetchColumn(
			'SELECT 1
            FROM subscriptions
            WHERE id = ?
            AND user_id = ?',
			[$this->id, $this->owner->id()]
		);
	}
}