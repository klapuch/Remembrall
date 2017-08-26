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
use Remembrall\Misc;
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class OwnedSubscription extends \Tester\TestCase {
	use TestCase\Database;

	public function testThrowingOnHandlingForeign() {
		$ex = Assert::exception(function() {
			(new Subscribing\OwnedSubscription(
				new Subscribing\FakeSubscription(),
				1,
				new Access\FakeUser('666'),
				$this->database
			))->cancel();
		}, \UnexpectedValueException::class);
		Assert::type(\Throwable::class, $ex->getPrevious());
		$ex = Assert::exception(function() {
			(new Subscribing\OwnedSubscription(
				new Subscribing\FakeSubscription(),
				1,
				new Access\FakeUser('666'),
				$this->database
			))->edit(new Time\FakeInterval(null, null, 'PT10M'));
		}, \UnexpectedValueException::class);
		Assert::type(\Throwable::class, $ex->getPrevious());
		Assert::exception(function() {
			(new Subscribing\OwnedSubscription(
				new Subscribing\FakeSubscription(),
				1,
				new Access\FakeUser('666'),
				$this->database
			))->notify();
		}, \UnexpectedValueException::class);
		$ex = Assert::exception(function() {
			(new Subscribing\OwnedSubscription(
				new Subscribing\FakeSubscription(),
				1,
				new Access\FakeUser('666'),
				$this->database
			))->print(new Output\FakeFormat(''));
		}, \UnexpectedValueException::class);
		Assert::type(\Throwable::class, $ex->getPrevious());
	}

	public function testPassingOnHandlingOwned() {
		(new Misc\SampleSubscription($this->database, ['user' => 666]))->try();
		Assert::noError(
			function() {
				(new Subscribing\OwnedSubscription(
					new Subscribing\FakeSubscription(),
					1,
					new Access\FakeUser('666'),
					$this->database
				))->cancel();
			}
		);
		Assert::noError(
			function() {
				(new Subscribing\OwnedSubscription(
					new Subscribing\FakeSubscription(),
					1,
					new Access\FakeUser('666'),
					$this->database
				))->edit(new Time\FakeInterval(null, null, 'PT10M'));
			}
		);
		Assert::noError(
			function() {
				(new Subscribing\OwnedSubscription(
					new Subscribing\FakeSubscription(),
					1,
					new Access\FakeUser('666'),
					$this->database
				))->notify();
			}
		);
		Assert::noError(
			function() {
				(new Subscribing\OwnedSubscription(
					new Subscribing\FakeSubscription(),
					1,
					new Access\FakeUser('666'),
					$this->database
				))->print(new Output\FakeFormat(''));
			}
		);
	}
}

(new OwnedSubscription)->run();