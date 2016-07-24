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

final class MySqlSubscribers extends TestCase\Database {
	public function testRegisteringBrandNewSubscriber() {
		$subscriber = (new Access\MySqlSubscribers(
			$this->database,
			new Security\FakeCipher()
		))->register('foo@bar.cz', 'passw0rt');
		$subscribers = $this->database->fetchAll(
			'SELECT id, email, password
			FROM subscribers'
		);
		Assert::equal(
			new Access\MySqlSubscriber(1, $this->database),
			$subscriber
		);
		Assert::count(1, $subscribers);
		Assert::same('foo@bar.cz', $subscribers[0]['email']);
		Assert::same('secret', $subscribers[0]['password']);
		Assert::same(1, $subscribers[0]['id']);
	}

	public function testRegistrationWithDuplicatedEmail() {
		$this->database->query(
			'INSERT INTO subscribers (id, email, password) VALUES
			(1, "foo@bar.cz", "secret")'
		);
		Assert::exception(
			function() {
				(new Access\MySqlSubscribers(
					$this->database,
					new Security\FakeCipher()
				))->register('foo@bar.cz', 'passw0rt');
			},
			\Remembrall\Exception\DuplicateException::class,
			'Email "foo@bar.cz" already exists'
		);
	}

    protected function prepareDatabase() {
		$this->purge(['subscribers']);
    }
}

(new MySqlSubscribers)->run();
