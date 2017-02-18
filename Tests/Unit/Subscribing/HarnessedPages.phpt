<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Subscribing;

use Klapuch\Uri;
use Remembrall\Model\Misc;
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class HarnessedPages extends TestCase\Mockery {
	public function testThroughCallback() {
		$uri = new Uri\FakeUri();
		$page = new Subscribing\FakePage();
		$addedPage = new Subscribing\FakePage();
		$origin = $this->mock(Subscribing\Pages::class);
		$callback = $this->mock(Misc\Callback::class);
		$callback->shouldReceive('invoke')
			->once()
			->with([$origin, 'add'], [$uri, $page])
			->andReturn($addedPage);
		Assert::noError(function() use($origin, $callback, $uri, $page) {
			(new Subscribing\HarnessedPages(
				$origin,
				$callback
			))->add($uri, $page);
		});
	}
}

(new HarnessedPages())->run();