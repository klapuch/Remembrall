<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Access;

use Remembrall\Model\Access;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class SecureForgottenPasswords extends TestCase\Database {
	public function testReminding() {
		(new Access\SecureForgottenPasswords(
			$this->database
		))->remind('foo@bar.cz');
		Assert::same(
			[
				'subscriber_id' => 1,
				'reminder_length' => 141,
				'reminded_at' => date('j.n.Y'),
				'used' => '0',
			],
			$this->database->fetch(
				'SELECT subscriber_id,
				LENGTH(reminder) AS reminder_length,
				DATE_FORMAT(reminded_at, "%e.%c.%Y") AS reminded_at,
				used
				FROM forgotten_passwords'
			)->toArray()
		);
	}

	protected function prepareDatabase() {
		$this->database->query('TRUNCATE forgotten_passwords');
		$this->database->query('TRUNCATE subscribers');
		$this->database->query(
			'INSERT INTO subscribers (`password`, email) VALUES ("123", "foo@bar.cz")'
		);
	}
}

(new SecureForgottenPasswords())->run();
