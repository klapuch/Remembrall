<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Subscribing;

use Klapuch\{
	Output, Time, Uri
};
use Remembrall\Model\Subscribing;
use Remembrall\Model\Misc;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class HarnessedSubscriptions extends TestCase\Mockery {
	public function testThroughCallback() {
		$uri = new Uri\FakeUri();
		$interval = new Time\FakeInterval();
		$iterator = new \ArrayIterator([]);
		$format = new Output\FakeFormat();
		$expression = '//p';
		$origin = $this->mock(Subscribing\Subscriptions::class);
		$callback = $this->mock(Misc\Callback::class);
		$callback->shouldReceive('invoke')
			->once()
			->with([$origin, 'subscribe'], [$uri, $expression, $interval]);
		Assert::noError(function() use($origin, $callback, $uri, $interval, $expression) {
			(new Subscribing\HarnessedSubscriptions(
				$origin,
				$callback
			))->subscribe($uri, $expression, $interval);
		});
		$callback->shouldReceive('invoke')
			->once()
			->with([$origin, 'getIterator'], [])
			->andReturn($iterator);
		Assert::noError(function() use($origin, $callback) {
			(new Subscribing\HarnessedSubscriptions(
				$origin,
				$callback
			))->getIterator();
		});
	}
}

(new HarnessedSubscriptions())->run();