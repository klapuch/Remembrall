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

	protected function allowedSteps() {
		$negativeInterval = new \DateInterval('PT4M');
		$negativeInterval->invert = 1;
		return [
			[new \DateInterval('PT4M'), 240],
			[new \DateInterval('PT4S'), 4],
			[new \DateInterval('PT1H'), 3600],
			[new \DateInterval('P2D'), 86400 * 2],
			[$negativeInterval, 240],
			[new \DateInterval('PT0M'), 0],
		];
	}

	protected function disallowedSteps() {
		return [
			[new \DateInterval('P1M')], // not supported
			[new \DateInterval('P1Y')], // not supported
		];
	}

	/**
	 * @dataProvider allowedSteps
	 */
	public function testStepsConvertedToSeconds(
		\DateInterval $actual,
		int $expected
	) {
		Assert::equal(
			$expected,
			(new Subscribing\DateTimeInterval(
				new \DateTimeImmutable('2000-01-01 01:01:01'),
				$actual
			))->step()
		);
	}

	/**
	 * @dataProvider disallowedSteps
	 */
	public function testStepsNotConvertedToSeconds(\DateInterval $actual) {
		Assert::exception(
			function() use ($actual) {
				(new Subscribing\DateTimeInterval(
					new \DateTimeImmutable('2000-01-01 01:01:01'),
					$actual
				))->step();
			},
			\OutOfRangeException::class,
			'Months or years can not be precisely transferred'
		);
	}

}

(new DateTimeInterval())->run();
