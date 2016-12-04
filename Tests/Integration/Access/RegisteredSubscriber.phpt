<?php
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Access;

use Remembrall\Model\Access;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class RegisteredSubscriber extends TestCase\Database {
	/**
	 * @throws Remembrall\Exception\NotFoundException User id "666" does not exist
	 */
	public function testUnknownUserId() {
		(new Access\RegisteredSubscriber(666, $this->database))->id();
	}

	public function testKnownUserId() {
		$this->database->query(
			"INSERT INTO users (id, email, password) VALUES
			(666, 'foo@bar.cz', 'password')"
		);
		Assert::same(
			666,
			(new Access\RegisteredSubscriber(666, $this->database))->id()
		);
	}

	public function testExistingEmail() {
		$this->database->query(
			"INSERT INTO users (id, email, password) VALUES
			(666, 'foo@bar.cz', 'password')"
		);
		Assert::same(
			'foo@bar.cz',
			(new Access\RegisteredSubscriber(666, $this->database))->email()
		);
	}

	/**
	 * @throws Remembrall\Exception\NotFoundException User id "666" does not exist
	 */
	public function testUnknownEmail() {
		(new Access\RegisteredSubscriber(666, $this->database))->email();
	}

	protected function prepareDatabase() {
		$this->purge(['users']);
	}
}

(new RegisteredSubscriber)->run();