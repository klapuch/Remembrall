<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Subscribing;

use Klapuch\Output;
use Klapuch\Time;
use Remembrall\Misc;
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class StoredSubscription extends \Tester\TestCase {
	use TestCase\Database;

	public function testCancelingWithoutAffectingOthers() {
		(new Misc\SampleSubscription($this->database))->try();
		(new Misc\SampleSubscription($this->database))->try();
		(new Misc\SamplePart($this->database))->try();
		(new Misc\SamplePart($this->database))->try();
		(new Subscribing\StoredSubscription(1, $this->database))->cancel();
		(new Misc\TableCount($this->database, 'subscriptions', 1))->assert();
		Assert::same(2, $this->database->query('SELECT id FROM subscriptions')->fetchColumn());
	}

	public function testCancelingUnknownWithoutEffect() {
		(new Misc\SampleSubscription($this->database))->try();
		(new Misc\SamplePart($this->database))->try();
		(new Misc\SamplePart($this->database))->try();
		$statement = $this->database->prepare('SELECT * FROM subscriptions');
		$statement->execute();
		$before = $statement->fetchAll();
		(new Subscribing\StoredSubscription(666, $this->database))->cancel();
		$statement->execute();
		$after = $statement->fetchAll();
		Assert::same($before, $after);
	}


	public function testEditingIntervalWithoutChangingLastUpdate() {
		(new Misc\SampleSubscription($this->database, ['last_update' => '2000-01-01 00:00:00']))->try();
		(new Misc\SamplePart($this->database))->try();
		(new Misc\SamplePart($this->database))->try();
		$id = 1;
		(new Subscribing\StoredSubscription(
			$id,
			$this->database
		))->edit(new Time\FakeInterval(null, null, 'PT10M'));
		$statement = $this->database->prepare('SELECT * FROM readable_subscriptions() WHERE id = ?');
		$statement->execute([$id]);
		$subscription = $statement->fetch();
		Assert::same('PT10M', $subscription['interval']);
		Assert::contains('2000-01-01 00:00:00', $subscription['last_update']);
	}

	public function testNotifying() {
		[$part, $subscription] = [3, 1];
		(new Misc\SampleSubscription($this->database, ['part' => $part]))->try();
		(new Misc\SamplePart($this->database))->try();
		(new Misc\SamplePart($this->database))->try();
		(new Misc\SamplePart($this->database))->try();
		(new Subscribing\StoredSubscription($subscription, $this->database))->notify();
		(new Misc\TableCount($this->database, 'notifications', 1))->assert();
		Assert::same($subscription, $this->database->query('SELECT subscription_id FROM notifications')->fetchColumn());
	}

	public function testNotifyingWithUpdatedSnapshot() {
		[$part, $subscription] = [3, 1];
		(new Misc\SampleSubscription($this->database, ['part' => $part]))->try();
		(new Misc\SamplePart($this->database))->try();
		(new Misc\SamplePart($this->database))->try();
		(new Misc\SamplePart($this->database, ['snapshot' => 'facedown snap']))->try();
		(new Subscribing\StoredSubscription(
			$subscription,
			$this->database
		))->notify();
		$statement = $this->database->prepare('SELECT * FROM subscriptions WHERE id = ?');
		$statement->execute([$subscription]);
		Assert::same('facedown snap', $statement->fetch()['snapshot']);
	}

	public function testPrintingAll() {
		$this->database->exec(
			"INSERT INTO subscriptions (id, user_id, part_id, interval, last_update, snapshot) VALUES
			(1, 111, 3, 'PT1980S', '2000-01-01', 'abc_snap')"
		);
		$this->database->exec(
			"INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES
			(3, 'www.google.com', ROW('//p', 'css'), 'google content', 'google snap')"
		);
		$this->truncate(['part_visits']);
		$this->database->exec(
			"INSERT INTO part_visits (id, part_id, visited_at) VALUES
			(2, 3, '2017-08-19 13:23:46')"
		);
		$subscription = new Subscribing\StoredSubscription(1, $this->database);
		Assert::same(
			'|id|1||interval|PT33M||url|www.google.com||expression|//p||language|css||content|google content||last_update|2000-01-01T00:00:00Z||visited_at|2017-08-19T13:23:46Z|',
			$subscription->print(new Output\FakeFormat(''))->serialization()
		);
	}
}

(new StoredSubscription)->run();