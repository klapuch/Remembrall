<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Web;

use Klapuch\Dataset;
use Klapuch\Uri;
use Remembrall\Model\Misc;
use Remembrall\Model\Web;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class HarnessedParts extends TestCase\Mockery {
	public function testThroughCallback() {
		$uri = new Uri\FakeUri();
		$part = new Web\FakePart();
		$iterator = new \ArrayIterator([]);
		$count = 3;
		$expression = '//p';
		$origin = $this->mock(Web\Parts::class);
		$callback = $this->mock(Misc\Callback::class);
		$callback->shouldReceive('invoke')
			->once()
			->with([$origin, 'add'], [$part, $uri, $expression]);
		Assert::noError(function() use ($origin, $callback, $uri, $part, $expression) {
			(new Web\HarnessedParts(
				$origin,
				$callback
			))->add($part, $uri, $expression);
		});
		$selection = new Dataset\FakeSelection();
		$callback->shouldReceive('invoke')
			->once()
			->with([$origin, 'all'], [$selection])
			->andReturn($iterator);
		Assert::noError(function() use ($origin, $callback, $selection) {
			(new Web\HarnessedParts(
				$origin,
				$callback
			))->all($selection);
		});
		$callback->shouldReceive('invoke')
			->once()
			->with([$origin, 'count'], [])
			->andReturn($count);
		Assert::noError(function() use ($origin, $callback) {
			(new Web\HarnessedParts(
				$origin,
				$callback
			))->count();
		});
	}
}

(new HarnessedParts())->run();