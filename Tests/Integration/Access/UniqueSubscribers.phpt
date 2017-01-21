<?php
declare(strict_types = 1);
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
	public function testRegisteringBrandNewOne() {
		$subscriber = (new Access\UniqueSubscribers(
			$this->database,
			new Encryption\FakeCipher()
		))->register('foo@bar.cz', 'passw0rt');
		Assert::equal(
			new Access\RegisteredSubscriber(1, $this->database),
			$subscriber
		);
		$statement = $this->database->prepare('SELECT * FROM users');
		$statement->execute();
		$users = $statement->fetchAll();
		Assert::count(1, $users);
		Assert::same('foo@bar.cz', $users[0]['email']);
		Assert::same('secret', $users[0]['password']);
		Assert::same(1, $users[0]['id']);
	}

	public function testRegisteringMultipleDifferentEmails() {
		$subscribers = new Access\UniqueSubscribers(
			$this->database,
			new Encryption\FakeCipher()
		);
		$subscribers->register('foo@bar.cz', 'ultra secret password');
		$subscribers->register('bar@foo.cz', 'weak password');
		$statement = $this->database->prepare('SELECT * FROM users');
		$statement->execute();
		$users = $statement->fetchAll();
		Assert::count(2, $users);
		Assert::same('foo@bar.cz', $users[0]['email']);
		Assert::same('secret', $users[0]['password']);
		Assert::same(1, $users[0]['id']);
		Assert::same('bar@foo.cz', $users[1]['email']);
		Assert::same('secret', $users[1]['password']);
		Assert::same(2, $users[1]['id']);
	}

	public function testThrowingOnDuplicatedEmail() {
		$subscribers = new Access\UniqueSubscribers(
			$this->database,
			new Encryption\FakeCipher()
		);
		$register = function() use($subscribers) {
			$subscribers->register('foo@bar.cz', 'password');
		};
		$register();
		$ex = Assert::exception(
			$register,
			\Remembrall\Exception\DuplicateException::class,
			'Email "foo@bar.cz" already exists'
		);
		Assert::type(\Throwable::class, $ex->getPrevious());
	}

	protected function prepareDatabase() {
		$this->purge(['users']);
	}
}

(new UniqueSubscribers())->run();