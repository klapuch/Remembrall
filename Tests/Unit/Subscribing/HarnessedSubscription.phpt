<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Subscribing;

use Klapuch\Output;
use Klapuch\Time;
use Remembrall\Model\Misc;
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class HarnessedSubscription extends \Tester\TestCase {
	use TestCase\Mockery;

	public function testThroughCallback() {
		$origin = $this->mock(Subscribing\Subscription::class);
		$callback = $this->mock(Misc\Callback::class);
		$callback->shouldReceive('invoke')
			->once()
			->with([$origin, 'cancel'], []);
		Assert::noError(function() use ($origin, $callback) {
			(new Subscribing\HarnessedSubscription(
				$origin,
				$callback
			))->cancel();
		});
		$callback->shouldReceive('invoke')
			->once()
			->with([$origin, 'notify'], []);
		Assert::noError(function() use ($origin, $callback) {
			(new Subscribing\HarnessedSubscription(
				$origin,
				$callback
			))->notify();
		});
		$format = new Output\FakeFormat();
		$callback->shouldReceive('invoke')
			->once()
			->with([$origin, 'print'], [$format])
			->andReturn($format);
		Assert::noError(function() use ($origin, $callback, $format) {
			(new Subscribing\HarnessedSubscription(
				$origin,
				$callback
			))->print($format);
		});
		$interval = new Time\FakeInterval();
		$callback->shouldReceive('invoke')
			->once()
			->with([$origin, 'edit'], [$interval]);
		Assert::noError(function() use ($origin, $callback, $interval) {
			(new Subscribing\HarnessedSubscription(
				$origin,
				$callback
			))->edit($interval);
		});
	}
}

(new HarnessedSubscription())->run();