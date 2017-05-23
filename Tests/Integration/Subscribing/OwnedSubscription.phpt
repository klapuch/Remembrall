<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Subscribing;

use Klapuch\Access;
use Klapuch\Output;
use Klapuch\Time;
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class OwnedSubscription extends TestCase\Database {
	public function testThrowingOnHandlingForeign() {
		Assert::exception(function() {
			(new Subscribing\OwnedSubscription(
				new Subscribing\FakeSubscription(),
				1,
				new Access\FakeUser(666),
				$this->database
			))->cancel();
		}, \Remembrall\Exception\NotFoundException::class);
		Assert::exception(function() {
			(new Subscribing\OwnedSubscription(
				new Subscribing\FakeSubscription(),
				1,
				new Access\FakeUser(666),
				$this->database
			))->edit(new Time\FakeInterval(null, null, 'PT10M'));
		}, \Remembrall\Exception\NotFoundException::class);
		Assert::exception(function() {
			(new Subscribing\OwnedSubscription(
				new Subscribing\FakeSubscription(),
				1,
				new Access\FakeUser(666),
				$this->database
			))->notify();
		}, \Remembrall\Exception\NotFoundException::class);
		Assert::exception(function() {
			(new Subscribing\OwnedSubscription(
				new Subscribing\FakeSubscription(),
				1,
				new Access\FakeUser(666),
				$this->database
			))->print(new Output\FakeFormat(''));
		}, \Remembrall\Exception\NotFoundException::class);
	}

	public function testHandlingOwned() {
		$this->database->exec(
			"INSERT INTO subscriptions (id, user_id, part_id, interval, last_update, snapshot) VALUES
			(2, 666, 4, 'PT3M', NOW(), '')"
		);
		Assert::noError(
			function() {
				(new Subscribing\OwnedSubscription(
					new Subscribing\FakeSubscription(),
					2,
					new Access\FakeUser(666),
					$this->database
				))->cancel();
			}
		);
		Assert::noError(
			function() {
				(new Subscribing\OwnedSubscription(
					new Subscribing\FakeSubscription(),
					2,
					new Access\FakeUser(666),
					$this->database
				))->edit(new Time\FakeInterval(null, null, 'PT10M'));
			}
		);
		Assert::noError(
			function() {
				(new Subscribing\OwnedSubscription(
					new Subscribing\FakeSubscription(),
					2,
					new Access\FakeUser(666),
					$this->database
				))->notify();
			}
		);
		Assert::noError(
			function() {
				(new Subscribing\OwnedSubscription(
					new Subscribing\FakeSubscription(),
					2,
					new Access\FakeUser(666),
					$this->database
				))->print(new Output\FakeFormat(''));
			}
		);
	}

	protected function prepareDatabase(): void {
		$this->purge(['subscriptions']);
	}
}

(new OwnedSubscription)->run();