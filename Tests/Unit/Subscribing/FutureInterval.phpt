<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Subscribing;

use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
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

	protected function pastSteps() {
		return [
			[new \DateInterval('PT2M'), 1],
			[new \DateInterval('PT2M'), true],
			[new \DateInterval('PT2M'), 5],
			[new \DateInterval('PT2M'), -1],
			[new \DateInterval('PT2M'), '1'],
		];
	}

	/**
	 * @dataProvider pastSteps
	 * @throws \OutOfRangeException Step must points to the future
	 */
	public function testStepPointingToPast(\DateInterval $step, $invert) {
		$step->invert = $invert;
		(new Subscribing\FutureInterval(
			new Subscribing\FakeInterval(null, null, $step)
		))->step();
	}

	public function testStepPointingToFuture() {
		Assert::noError(function() {
			(new Subscribing\FutureInterval(
				new Subscribing\FakeInterval(null, null, new \DateInterval('PT2M'))
			))->step();
		});
	}

	/**
	 * @throws \OutOfRangeException Begin step must points to the future
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
