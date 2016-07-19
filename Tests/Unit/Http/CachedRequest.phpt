<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Http;

use Remembrall\Model\{
	Http, Subscribing
};
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class CachedRequest extends TestCase\Mockery {
	/** @var \Mockery\Mock */
	private $cache;

	public function setUp() {
		parent::setUp();
		$this->cache = $this->mockery('Nette\Caching\IStorage');
	}

	public function testCaching() {
		$page = new Subscribing\FakePage();
		$this->cache->shouldReceive('read')
			->andReturn($page)
			->with('Remembrall\Model\Http\CachedRequest::send')
			->times(4);
		$request = new Http\CachedRequest(
			new Http\FakeRequest(),
			$this->cache
		);

		Assert::same($page, $request->send());
		Assert::same($page, $request->send());
	}
}

(new CachedRequest())->run();
