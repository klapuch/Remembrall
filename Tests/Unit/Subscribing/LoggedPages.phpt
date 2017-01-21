<?php
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Subscribing;

use Klapuch\Uri;
use Remembrall\Model\Subscribing;
use Remembrall\Model\Misc;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class LoggedPages extends TestCase\Mockery {
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
			(new Subscribing\LoggedPages($origin, $callback))->add($uri, $page);
		});
	}
}

(new LoggedPages())->run();