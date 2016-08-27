<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Subscribing;

use Remembrall\Model\Subscribing;
use Tester,
	Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class FutureInterval extends Tester\TestCase {
	/**
	 * @throws \OutOfRangeException Next step must points to the future
	 */
	public function testNextPointingToPast() {
		(new Subscribing\FutureInterval(
			new Subscribing\FakeInterval(
				(new \DateTime())->add(new \DateInterval('P10D')),
				new \DateTimeImmutable('1900-01-01 01:01:01')
			)
		))->next();
	}

	/**
	 * @throws \OutOfRangeException Next step must points to the future
	 */
	public function testNextPointingToSameTime() {
		$future = (new \DateTime())->add(new \DateInterval('P10D'));
		(new Subscribing\FutureInterval(
			new Subscribing\FakeInterval(
				$future,
				$future
			)
		))->next();
	}

	public function testNextPointingToFuture() {
		Assert::noError(function() {
			(new Subscribing\FutureInterval(
				new Subscribing\FakeInterval(
					(new \DateTime())->add(new \DateInterval('P5D')),
					(new \DateTime())->add(new \DateInterval('P10D'))
				)
			))->next();
		});
	}

	public function testStepPointingToFuture() {
		Assert::noError(function() {
			(new Subscribing\FutureInterval(
				new Subscribing\FakeInterval(null, null, 120)
			))->step();
		});
	}

	/**
	 * @throws \OutOfRangeException Start interval must points to the future
	 */
	public function testPastStart() {
		(new Subscribing\FutureInterval(
			new Subscribing\FakeInterval(new \DateTime('2000-01-01 01:01:01'))
		))->start();
	}

	public function testFutureStart() {
		Assert::noError(function() {
			(new Subscribing\FutureInterval(
				new Subscribing\FakeInterval((new \DateTime())->add(new \DateInterval('P10D')))
			))->start();
		});
	}
}

(new FutureInterval())->run();
