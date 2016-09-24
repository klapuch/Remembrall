<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
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
		$subscribers = $this->database->fetchAll(
			'SELECT id, email, password
			FROM subscribers'
		);
		Assert::equal(
			new Access\RegisteredSubscriber(1, $this->database),
			$subscriber
		);
		Assert::count(1, $subscribers);
		Assert::same('foo@bar.cz', $subscribers[0]['email']);
		Assert::same('secret', $subscribers[0]['password']);
		Assert::same(1, $subscribers[0]['id']);
	}

	public function testRegisteringWithDuplicatedEmail() {
		$this->database->query(
			"INSERT INTO subscribers (email, password) VALUES
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
		$this->purge(['subscribers']);
    }
}

(new UniqueSubscribers())->run();
