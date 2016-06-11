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
	 * @throws \OutOfRangeException Interval must points to the future
	 */
	public function testNextPointingToPast() {
		(new Subscribing\FutureInterval(
			new Subscribing\FakeInterval(
				new \DateTimeImmutable('2000-01-01 01:01:01'),
				new Subscribing\FakeInterval(
					new \DateTimeImmutable('1900-01-01 01:01:01'),
					new Subscribing\FakeInterval()
				)
			)
		))->next();
	}

	/**
	 * @throws \OutOfRangeException Interval must points to the future
	 */
	public function testNextPointingToSameTime() {
		(new Subscribing\FutureInterval(
			new Subscribing\FakeInterval(
				new \DateTimeImmutable('2000-01-01 01:01:01'),
				new Subscribing\FakeInterval(
					new \DateTimeImmutable('2000-01-01 01:01:01'),
					new Subscribing\FakeInterval()
				)
			)
		))->next();
	}

	public function testNextPointingToFuture() {
		(new Subscribing\FutureInterval(
			new Subscribing\FakeInterval(
				new \DateTimeImmutable('2000-01-01 01:01:01'),
				new Subscribing\FakeInterval(
					new \DateTimeImmutable('2100-01-01 01:01:01'),
					new Subscribing\FakeInterval()
				)
			)
		))->next();
		Assert::true(true);
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
	 * @throws \OutOfRangeException Interval must points to the future
	 */
	public function testStepPointingToPast(\DateInterval $step, $invert) {
		$step->invert = $invert;
		(new Subscribing\FutureInterval(
			new Subscribing\FakeInterval(null, null, $step)
		))->step();
	}

	public function testStepPointingToFuture() {
		(new Subscribing\FutureInterval(
			new Subscribing\FakeInterval(null, null, new \DateInterval('PT2M'))
		))->step();
		Assert::true(true);
	}
}

(new FutureInterval())->run();
