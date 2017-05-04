<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Access;

use Klapuch\Access;
use Klapuch\Encryption;
use Remembrall\Model;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ParticipatedUsers extends TestCase\Database {
	public function testPassingWithNoNeededTransfer() {
		$this->database->exec(
			"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			('you@participant.cz', 1, 'abc', NOW(), TRUE, NOW())"
		);
		$this->database->exec(
			"INSERT INTO subscriptions (user_id, part_id, interval, last_update, snapshot) VALUES
			(3, 3, 'PT10S', NOW(), 'abc')"
		);
		(new Model\Access\ParticipatedUsers(
			new Access\UniqueUsers($this->database, new Encryption\FakeCipher()),
			$this->database
		))->register('me@participant.cz', '123', 'member');
		Assert::count(1, $this->database->query('SELECT * FROM participants')->fetchAll());
		Assert::count(1, $this->database->query('SELECT * FROM invitation_attempts')->fetchAll());
		Assert::count(1, $this->database->query('SELECT * FROM users')->fetchAll());
		Assert::count(1, $this->database->query('SELECT * FROM subscriptions')->fetchAll());
	}

	public function testTransferringInheritSubscriptions() {
		$this->database->exec(
			"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			('me@participant.cz', 1, 'abc', NOW(), TRUE, NOW()),
			('me@participant.cz', 2, 'abc', NOW(), FALSE, NULL),
			('me@participant.cz', 3, 'abc', NOW(), TRUE, NOW()),
			('you@participant.cz', 3, 'abc', NOW(), TRUE, NOW())"
		);
		$this->database->exec(
			"INSERT INTO subscriptions (user_id, part_id, interval, last_update, snapshot) VALUES
			(3, 3, 'PT10S', NOW(), 'abc'),
			(3, 4, 'PT20S', NOW(), 'def'),
			(3, 5, 'PT30S', NOW(), 'ghi')"
		);
		(new Model\Access\ParticipatedUsers(
			new Access\UniqueUsers($this->database, new Encryption\FakeCipher()),
			$this->database
		))->register('me@participant.cz', '123', 'member');
		Assert::count(2, $this->database->query('SELECT * FROM participants')->fetchAll());
		Assert::count(2, $this->database->query('SELECT * FROM invitation_attempts')->fetchAll());
		Assert::count(1, $this->database->query('SELECT * FROM users')->fetchAll());
		$subscriptions = $this->database->query('SELECT * FROM subscriptions ORDER BY id')->fetchAll();
		Assert::count(5, $subscriptions);
		Assert::same(
			[
				'id' => 4,
				'user_id' => 1,
				'part_id' => $subscriptions[0]['part_id'],
				'interval' => $subscriptions[0]['interval'],
				'last_update' => $subscriptions[0]['last_update'],
				'snapshot' => $subscriptions[0]['snapshot'],
			],
			$subscriptions[3]
		);
		Assert::same(
			[
				'id' => 5,
				'user_id' => 1,
				'part_id' => $subscriptions[2]['part_id'],
				'interval' => $subscriptions[2]['interval'],
				'last_update' => $subscriptions[2]['last_update'],
				'snapshot' => $subscriptions[2]['snapshot'],
			],
			$subscriptions[4]
		);
	}

	public function testTransferringWithCaseInsensitiveEmail() {
		$this->database->exec(
			"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			('ME@participant.cz', 1, 'abc', NOW(), TRUE, NOW())"
		);
		$this->database->exec(
			"INSERT INTO subscriptions (user_id, part_id, interval, last_update, snapshot) VALUES
			(3, 3, 'PT10S', NOW(), 'abc')"
		);
		(new Model\Access\ParticipatedUsers(
			new Access\UniqueUsers($this->database, new Encryption\FakeCipher()),
			$this->database
		))->register('me@participant.cz', '123', 'member');
		Assert::count(0, $this->database->query('SELECT * FROM participants')->fetchAll());
		Assert::count(0, $this->database->query('SELECT * FROM invitation_attempts')->fetchAll());
		Assert::count(2, $this->database->query('SELECT * FROM subscriptions')->fetchAll());
		$this->prepareDatabase();
		$this->database->exec(
			"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			('me@participant.cz', 1, 'abc', NOW(), TRUE, NOW())"
		);
		$this->database->exec(
			"INSERT INTO subscriptions (user_id, part_id, interval, last_update, snapshot) VALUES
			(3, 3, 'PT10S', NOW(), 'abc')"
		);
		(new Model\Access\ParticipatedUsers(
			new Access\UniqueUsers($this->database, new Encryption\FakeCipher()),
			$this->database
		))->register('ME@participant.cz', '123', 'member');
		Assert::count(0, $this->database->query('SELECT * FROM participants')->fetchAll());
		Assert::count(0, $this->database->query('SELECT * FROM invitation_attempts')->fetchAll());
		Assert::count(2, $this->database->query('SELECT * FROM subscriptions')->fetchAll());
	}

	protected function prepareDatabase(): void {
		$this->purge(['users', 'participants', 'invitation_attempts', 'subscriptions']);
	}
}

(new ParticipatedUsers)->run();