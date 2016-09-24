<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;
use Klapuch\Time;

require __DIR__ . '/../../bootstrap.php';

final class PostgresSubscription extends TestCase\Database {
	public function testCancelingWithoutAffectingOthers() {
		(new Subscribing\PostgresSubscription(
			1,
			$this->database
		))->cancel();
		$subscriptions = $this->database->fetchAll('SELECT * FROM subscriptions');
		Assert::count(1, $subscriptions);
		Assert::same(2, $subscriptions[0]['id']);
	}

	public function testEditingIntervalWithoutChangingLastUpdate() {
		(new Subscribing\PostgresSubscription(
			1,
			$this->database
		))->edit(new Time\FakeInterval(null, null, 'PT10M'));
		$subscription = $this->database->fetch(
			'SELECT * FROM subscriptions WHERE id = 1'
		);
		Assert::same('PT10M', $subscription['interval']);
		Assert::same('2000-01-01 00:00:00', $subscription['last_update']);
	}

	public function testNotifyingOnGivenSubscription() {
		(new Subscribing\PostgresSubscription(
			1,
			$this->database
		))->notify();
		$notifications = $this->database->fetchAll(
			'SELECT * FROM notifications'
		);
		Assert::count(1, $notifications);
		Assert::same(1, $notifications[0]['subscription_id']);
	}

	protected function prepareDatabase() {
		$this->purge(['subscriptions', 'notifications']);
		$this->database->query(
			"INSERT INTO subscriptions (subscriber_id, part_id, interval, last_update) VALUES
			(111, 3, 'PT2M', '2000-01-01'),
			(666, 4, 'PT3M', '2000-01-01')"
		);
	}
}

(new PostgresSubscription)->run();
