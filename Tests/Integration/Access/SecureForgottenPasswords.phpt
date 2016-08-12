<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Access;

use Klapuch\Encryption;
use Remembrall\Model\Access;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class SecureForgottenPasswords extends TestCase\Database {
	public function testReminding() {
		(new Access\SecureForgottenPasswords(
			$this->database,
			new Encryption\FakeCipher()
		))->remind('foo@bar.cz');
		Assert::same(
			[
				'subscriber_id' => 1,
				'reminder_length' => 141,
				'used' => false,
			],
			$this->database->fetch(
				'SELECT subscriber_id, LENGTH(reminder) AS reminder_length, used
				FROM forgotten_passwords
				WHERE reminded_at <= NOW()'
			)
		);
	}

	protected function prepareDatabase() {
		$this->purge(['forgotten_passwords', 'subscribers']);
		$this->database->query(
			"INSERT INTO subscribers (id, email, password) VALUES
			(1, 'foo@bar.cz', '123')"
		);
	}
}

(new SecureForgottenPasswords())->run();
