<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Subscribing;

use Remembrall\Model\Subscribing;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class DateTimeInterval extends Tester\TestCase {
	public function testStart() {
		Assert::equal(
			new \DateTimeImmutable('2000-01-01 01:01:01'),
			(new Subscribing\DateTimeInterval(
				new \DateTimeImmutable('2000-01-01 01:01:01'),
				new \DateInterval('PT2M')
			))->start()
		);
	}

	public function testNextStep() {
		Assert::equal(
			new \DateTimeImmutable('2000-01-01 01:05:01'),
			(new Subscribing\DateTimeInterval(
				new \DateTimeImmutable('2000-01-01 01:01:01'),
				new \DateInterval('PT4M')
			))->next()
		);
	}

	protected function steps() {
		$negativeInterval = new \DateInterval('PT4M');
		$negativeInterval->invert = 1;
		return [
			[new \DateInterval('PT4M')],
			[$negativeInterval],
			[new \DateInterval('PT0M')],
		];
	}

	/**
	 * @dataProvider steps
	 */
	public function testTwoWayStepsWithoutError(\DateInterval $step) {
		Assert::equal(
			$step,
			(new Subscribing\DateTimeInterval(
				new \DateTimeImmutable('2000-01-01 01:01:01'),
				$step
			))->step()
		);
	}
}

(new DateTimeInterval())->run();
