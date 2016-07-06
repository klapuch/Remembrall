<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Subscribing;

use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ReportedParts extends TestCase\Mockery {
	public function testReportingSubscribedPart() {
		$parts = $this->mockery(Subscribing\Parts::class);
		$part = new Subscribing\FakePart();
		$interval = new Subscribing\FakeInterval();
		$subscribedPart = new Subscribing\FakePart();
		$parts->shouldReceive('subscribe')
			->with($part, $interval)
			->once()
			->andReturn($subscribedPart);
		$reports = $this->mockery(Subscribing\Reports::class);
		$reports->shouldReceive('archive')
			->with($subscribedPart)
			->once();
		Assert::same(
			$subscribedPart,
			(new Subscribing\ReportedParts($parts, $reports))
				->subscribe($part, $interval)
		);
	}

	public function testArchivingNewPart() {
		$parts = $this->mockery(Subscribing\Parts::class);
		$oldPart = new Subscribing\FakePart();
		$newPart = new Subscribing\FakePart();
		$parts->shouldReceive('replace')
			->with($oldPart, $newPart)
			->once();
		$reports = $this->mockery(Subscribing\Reports::class);
		$reports->shouldReceive('archive')
			->with($newPart)
			->once();
		Assert::noError(function() use($parts, $reports, $oldPart, $newPart) {
			(new Subscribing\ReportedParts($parts, $reports))
				->replace($oldPart, $newPart);
		});
	}
}

(new ReportedParts())->run();
