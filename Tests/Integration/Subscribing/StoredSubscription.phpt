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
		$this->database->exec(
			"INSERT INTO subscriptions (id, user_id, part_id, interval, last_update, snapshot) VALUES
			(1, 111, 3, 'PT2M', '2000-01-01', ''),
			(2, 666, 4, 'PT3M', '2000-01-01', '')"
		);
		$this->database->exec(
			"INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES
			(3, 'www.facedown.cz', ROW('//p', 'xpath'), 'facedown content', 'facedown snap'),
			(4, 'www.google.com', ROW('//p', 'xpath'), 'google content', 'google snap')"
		);
		(new Subscribing\StoredSubscription(1, $this->database))->cancel();
		(new Misc\TableCount($this->database, 'subscriptions', 1))->assert();
		Assert::same(2, $this->database->query('SELECT id FROM subscriptions')->fetchColumn());
	}

	public function testCancelingUnknownWithoutEffect() {
		$this->database->exec(
			"INSERT INTO subscriptions (id, user_id, part_id, interval, last_update, snapshot) VALUES
			(1, 111, 3, 'PT2M', '2000-01-01', ''),
			(2, 666, 4, 'PT3M', '2000-01-01', '')"
		);
		$this->database->exec(
			"INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES
			(3, 'www.facedown.cz', ROW('//p', 'xpath'), 'facedown content', 'facedown snap'),
			(4, 'www.google.com', ROW('//p', 'xpath'), 'google content', 'google snap')"
		);
		$statement = $this->database->prepare('SELECT * FROM subscriptions');
		$statement->execute();
		$before = $statement->fetchAll();
		(new Subscribing\StoredSubscription(666, $this->database))->cancel();
		$statement->execute();
		$after = $statement->fetchAll();
		Assert::same($before, $after);
	}


	public function testEditingIntervalWithoutChangingLastUpdate() {
		$this->database->exec(
			"INSERT INTO subscriptions (id, user_id, part_id, interval, last_update, snapshot) VALUES
			(1, 111, 3, 'PT2M', '2000-01-01', ''),
			(2, 666, 4, 'PT3M', '2000-01-01', '')"
		);
		$this->database->exec(
			"INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES
			(3, 'www.facedown.cz', ROW('//p', 'xpath'), 'facedown content', 'facedown snap'),
			(4, 'www.google.com', ROW('//p', 'xpath'), 'google content', 'google snap')"
		);
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
		$this->database->exec(
			"INSERT INTO subscriptions (id, user_id, part_id, interval, last_update, snapshot) VALUES
			(1, 111, 3, 'PT2M', '2000-01-01', ''),
			(2, 666, 4, 'PT3M', '2000-01-01', '')"
		);
		$this->database->exec(
			"INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES
			(3, 'www.facedown.cz', ROW('//p', 'xpath'), 'facedown content', 'facedown snap'),
			(4, 'www.google.com', ROW('//p', 'xpath'), 'google content', 'google snap')"
		);
		$id = 1;
		(new Subscribing\StoredSubscription($id, $this->database))->notify();
		(new Misc\TableCount($this->database, 'notifications', 1))->assert();
		Assert::same($id, $this->database->query('SELECT subscription_id FROM notifications')->fetchColumn());
	}

	public function testNotifyingWithUpdatedSnapshot() {
		$this->database->exec(
			"INSERT INTO subscriptions (id, user_id, part_id, interval, last_update, snapshot) VALUES
			(1, 111, 3, 'PT2M', '2000-01-01', ''),
			(2, 666, 4, 'PT3M', '2000-01-01', '')"
		);
		$this->database->exec(
			"INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES
			(3, 'www.facedown.cz', ROW('//p', 'xpath'), 'facedown content', 'facedown snap'),
			(4, 'www.google.com', ROW('//p', 'xpath'), 'google content', 'google snap')"
		);
		$id = 1;
		(new Subscribing\StoredSubscription(
			$id,
			$this->database
		))->notify();
		$statement = $this->database->prepare('SELECT * FROM subscriptions WHERE id = ?');
		$statement->execute([$id]);
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