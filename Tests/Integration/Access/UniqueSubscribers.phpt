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
	public function testRegisteringBrandNew() {
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

	public function testRegisteringToOthers() {
		$this->database->exec(
			"INSERT INTO users (email, password) VALUES
			('foo@bar.cz', 'secret')"
		);
		$subscriber = (new Access\UniqueSubscribers(
			$this->database,
			new Encryption\FakeCipher()
		))->register('bar@foo.cz', 'passw0rt');
		Assert::equal(
			new Access\RegisteredSubscriber(2, $this->database),
			$subscriber
		);
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

	public function testThrowingOnEmailDuplication() {
		$email = 'foo@bar.cz';
		$statement = $this->database->prepare(
			"INSERT INTO users (email, password) VALUES
			(?, 'secret')"
		);
		$statement->execute([$email]);
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