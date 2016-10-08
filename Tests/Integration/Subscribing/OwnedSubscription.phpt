<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use Remembrall\Model\{
	Subscribing, Access
};
use Remembrall\TestCase;
use Tester\Assert;
use Klapuch\Time;

require __DIR__ . '/../../bootstrap.php';

final class OwnedSubscription extends TestCase\Database {
	/**
	 * @throws \Remembrall\Exception\NotFoundException You can not cancel foreign subscription
	 */
	public function testCancelingForeign() {
		(new Subscribing\OwnedSubscription(
			new Subscribing\FakeSubscription(),
			1,
			new Access\FakeSubscriber(666),
			$this->database
		))->cancel();
	}

	public function testCancelingOwned() {
		Assert::noError(function() {
			(new Subscribing\OwnedSubscription(
				new Subscribing\FakeSubscription(),
				2,
				new Access\FakeSubscriber(666),
				$this->database
			))->cancel();
		});
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException You can not edit foreign subscription
	 */
	public function testEditingForeign() {
		(new Subscribing\OwnedSubscription(
			new Subscribing\FakeSubscription(),
			1,
			new Access\FakeSubscriber(666),
			$this->database
		))->edit(new Time\FakeInterval(null, null, 'PT10M'));
	}

	public function testEditingOwned() {
		Assert::noError(function() {
			(new Subscribing\OwnedSubscription(
				new Subscribing\FakeSubscription(),
				2,
				new Access\FakeSubscriber(666),
				$this->database
			))->edit(new Time\FakeInterval(null, null, 'PT10M'));
		});
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException You can not be notified on foreign subscription
	 */
	public function testNotifyingForeign() {
		(new Subscribing\OwnedSubscription(
			new Subscribing\FakeSubscription(),
			1,
			new Access\FakeSubscriber(666),
			$this->database
		))->notify();
	}

	public function testNotifyingOwned() {
		Assert::noError(function() {
			(new Subscribing\OwnedSubscription(
				new Subscribing\FakeSubscription(),
				2,
				new Access\FakeSubscriber(666),
				$this->database
			))->notify();
		});
	}

	protected function prepareDatabase() {
		$this->purge(['subscriptions']);
		$this->database->query(
			"INSERT INTO subscriptions (subscriber_id, part_id, interval, last_update, snapshot) VALUES
			(111, 3, 'PT2M', NOW(), ''),
			(666, 4, 'PT3M', NOW(), '')"
		);
	}
}

(new OwnedSubscription)->run();
