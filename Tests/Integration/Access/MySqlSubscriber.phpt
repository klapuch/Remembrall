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

final class MySqlSubscriber extends TestCase\Database {
	public function testId() {
		Assert::same(
			666,
			(new Access\MySqlSubscriber(
				 666, $this->database
			))->id()
		);
	}

	public function testEmail() {
		$this->database->query(
			'INSERT INTO subscribers (ID, email, `password`) VALUES
			(666, "foo@bar.cz", "password")'
		);
		Assert::same(
			'foo@bar.cz',
			(new Access\MySqlSubscriber(
				666, $this->database
			))->email()
		);
	}

    protected function prepareDatabase() {
        $this->database->query('TRUNCATE subscribers');
    }
}

(new MySqlSubscriber)->run();
