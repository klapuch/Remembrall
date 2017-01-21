<?php
declare(strict_types = 1);
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
	 * @throws \Remembrall\Exception\NotFoundException User id "666" does not exist
	 */
	public function testThrowingOnUnknownId() {
		(new Access\RegisteredSubscriber(666, $this->database))->id();
	}

	public function testGatheringKnownUserId() {
		$id = 666;
		$statement = $this->database->prepare(
			"INSERT INTO users (id, email, password) VALUES
			(?, 'foo@bar.cz', 'password')"
		);
		$statement->execute([$id]);
		Assert::same(
			$id,
			(new Access\RegisteredSubscriber($id, $this->database))->id()
		);
	}

	public function testGatheringExistingEmail() {
		$id = 666;
		$statement = $this->database->prepare(
			"INSERT INTO users (id, email, password) VALUES
			(?, 'foo@bar.cz', 'password')"
		);
		$statement->execute([$id]);
		Assert::same(
			'foo@bar.cz',
			(new Access\RegisteredSubscriber($id, $this->database))->email()
		);
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException User id "666" does not exist
	 */
	public function testThrowingOnUnknownEmail() {
		(new Access\RegisteredSubscriber(666, $this->database))->email();
	}

	protected function prepareDatabase() {
		$this->purge(['users']);
	}
}

(new RegisteredSubscriber)->run();