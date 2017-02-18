<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Subscribing;

use Klapuch\Output;
use Klapuch\Dataset;
use Klapuch\Uri;
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
		$selection = new Dataset\FakeSelection();
		$callback->shouldReceive('invoke')
			->once()
			->with([$origin, 'iterate'], [$selection])
			->andReturn($iterator);
		Assert::noError(function() use($origin, $callback, $selection) {
			(new Subscribing\HarnessedParts(
				$origin, $callback
			))->iterate($selection);
		});
	}
}

(new HarnessedParts())->run();