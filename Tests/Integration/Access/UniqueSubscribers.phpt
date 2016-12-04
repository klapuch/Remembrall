<?php
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Access;

use Klapuch\{
	Encryption, Storage
};
use Remembrall\Model\Access;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class UniqueSubscribers extends TestCase\Database {
	public function testRegisteringBrandNewSubscriber() {
		$subscriber = (new Access\UniqueSubscribers(
			$this->database,
			new Encryption\FakeCipher()
		))->register('foo@bar.cz', 'passw0rt');
		$subscribers = $this->database->fetchAll('SELECT * FROM users');
		Assert::equal(
			new Access\RegisteredSubscriber(1, $this->database),
			$subscriber
		);
		Assert::count(1, $subscribers);
		Assert::same('foo@bar.cz', $subscribers[0]['email']);
		Assert::same('secret', $subscribers[0]['password']);
		Assert::same(1, $subscribers[0]['id']);
	}

	public function testRegisteringToOthers() {
		$this->database->query(
			"INSERT INTO users (email, password) VALUES
			('foo@bar.cz', 'secret')"
		);
		$subscriber = (new Access\UniqueSubscribers(
			$this->database,
			new Encryption\FakeCipher()
		))->register('bar@foo.cz', 'passw0rt');
		$subscribers = $this->database->fetchAll('SELECT * FROM users');
		Assert::equal(
			new Access\RegisteredSubscriber(2, $this->database),
			$subscriber
		);
		Assert::count(2, $subscribers);
		Assert::same('foo@bar.cz', $subscribers[0]['email']);
		Assert::same('secret', $subscribers[0]['password']);
		Assert::same(1, $subscribers[0]['id']);
		Assert::same('bar@foo.cz', $subscribers[1]['email']);
		Assert::same('secret', $subscribers[1]['password']);
		Assert::same(2, $subscribers[1]['id']);
	}

	public function testRegisteringWithDuplicatedEmail() {
		$this->database->query(
			"INSERT INTO users (email, password) VALUES
			('foo@bar.cz', 'secret')"
		);
		$ex = Assert::exception(
			function() {
				(new Access\UniqueSubscribers(
					$this->database,
					new Encryption\FakeCipher()
				))->register('foo@bar.cz', 'passw0rt');
			},
			\Remembrall\Exception\DuplicateException::class,
			'Email "foo@bar.cz" already exists'
		);
		Assert::type(Storage\UniqueConstraint::class, $ex->getPrevious());
	}

	protected function prepareDatabase() {
		$this->purge(['users']);
	}
}

(new UniqueSubscribers())->run();