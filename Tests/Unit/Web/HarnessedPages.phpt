<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Web;

use Klapuch\Uri;
use Remembrall\Model\Misc;
use Remembrall\Model\Web;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class HarnessedPages extends TestCase\Mockery {
	public function testThroughCallback() {
		$uri = new Uri\FakeUri();
		$page = new Web\FakePage();
		$addedPage = new Web\FakePage();
		$origin = $this->mock(Web\Pages::class);
		$callback = $this->mock(Misc\Callback::class);
		$callback->shouldReceive('invoke')
			->once()
			->with([$origin, 'add'], [$uri, $page])
			->andReturn($addedPage);
		Assert::noError(function() use($origin, $callback, $uri, $page) {
			(new Web\HarnessedPages(
				$origin,
				$callback
			))->add($uri, $page);
		});
	}
}

(new HarnessedPages())->run();