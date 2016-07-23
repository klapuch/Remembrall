<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Access;

use Remembrall\Model\{
	Access, Security
};
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class MySqlRemindedPassword extends TestCase\Database {
	public function testChanging() {
		$this->database->query(
			'INSERT INTO forgotten_passwords
			(subscriber_id, used, reminder)
			VALUES (1, 0, "123456")'
		);
		(new Access\MySqlRemindedPassword(
			'123456',
			$this->database,
			new Security\FakeCipher()
		))->change('123456789');
		Assert::same(
			'secret',
			$this->database->fetchSingle(
				'SELECT `password`
				FROM subscribers
				WHERE ID = 1'
			)
		);
		Assert::truthy(
			$this->database->fetchSingle(
				'SELECT used
				FROM forgotten_passwords
				WHERE subscriber_id = 1'
			)
		);
	}

	protected function prepareDatabase() {
		$this->database->query('TRUNCATE forgotten_passwords');
		$this->database->query('TRUNCATE subscribers');
		$this->database->query(
			'INSERT INTO subscribers (email, `password`) VALUES ("foo@bar.cz", "123")'
		);
	}
}

(new MySqlRemindedPassword())->run();
