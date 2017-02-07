<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Subscribing;

use Klapuch\{
	Uri, Output
};
use Remembrall\Model\Subscribing;
use Remembrall\Model\Misc;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class HarnessedParts extends TestCase\Mockery {
	public function testThroughCallback() {
		$uri = new Uri\FakeUri();
		$part = new Subscribing\FakePart();
		$iterator = new \ArrayIterator([]);
		$format = new Output\FakeFormat();
		$expression = '//p';
		$origin = $this->mock(Subscribing\Parts::class);
		$callback = $this->mock(Misc\Callback::class);
		$callback->shouldReceive('invoke')
			->once()
			->with([$origin, 'add'], [$part, $uri, $expression]);
		Assert::noError(function() use($origin, $callback, $uri, $part, $expression) {
			(new Subscribing\HarnessedParts(
				$origin,
				$callback
			))->add($part, $uri, $expression);
		});
		$callback->shouldReceive('invoke')
			->once()
			->with([$origin, 'getIterator'], [])
			->andReturn($iterator);
		Assert::noError(function() use($origin, $callback) {
			(new Subscribing\HarnessedParts($origin, $callback))->getIterator();
		});
		$callback->shouldReceive('invoke')
			->once()
			->with([$origin, 'print'], [$format])
			->andReturn([$format]);
		Assert::noError(function() use($origin, $callback, $format) {
			(new Subscribing\HarnessedParts($origin, $callback))->print($format);
		});
	}
}

(new HarnessedParts())->run();