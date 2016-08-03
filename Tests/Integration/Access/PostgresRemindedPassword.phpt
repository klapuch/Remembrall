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

final class PostgresRemindedPassword extends TestCase\Database {
	public function testChanging() {
		$this->database->query(
			'INSERT INTO forgotten_passwords (subscriber_id, used, reminder, reminded_at) VALUES
			(1, FALSE, "123456", NOW())'
		);
		(new Access\PostgresRemindedPassword(
			'123456',
			$this->database,
			new Encryption\FakeCipher()
		))->change('123456789');
		Assert::same(
			'secret',
			$this->database->fetchSingle(
				'SELECT password
				FROM subscribers
				WHERE id = 1'
			)
		);
		Assert::true(
			$this->database->fetchSingle(
				'SELECT used
				FROM forgotten_passwords
				WHERE subscriber_id = 1'
			)
		);
	}

	protected function prepareDatabase() {
		$this->purge(['subscribers', 'forgotten_passwords']);
		$this->database->query(
			'INSERT INTO subscribers (id, email, password) VALUES
			(1, "foo@bar.cz", "123")'
		);
	}
}

(new PostgresRemindedPassword())->run();
