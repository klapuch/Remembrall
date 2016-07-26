<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Subscribing;

use Remembrall\Model\{
	Access, Subscribing
};
use Remembrall\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class CachedPart extends TestCase\Mockery {
	/** @var \Mockery\Mock */
	private $cache;

	public function setUp() {
		parent::setUp();
		$this->cache = $this->mockery('Nette\Caching\IStorage');
	}

	public function testCaching() {
		$content = '<p>XXX</p>';
		$equals = false;
		$fakePart = new Subscribing\FakePart(null, false, 'www.google.com');
		$this->cache->shouldReceive('read')
			->andReturn($content)
			->with('Remembrall\Model\Subscribing\CachedPart::content')
			->times(4);
		$this->cache->shouldReceive('read')
			->andReturn($fakePart)
			->with('Remembrall\Model\Subscribing\CachedPart::refresh')
			->times(4);
		$this->cache->shouldReceive('read')
			->andReturn($equals)
			->with('Remembrall\Model\Subscribing\CachedPart::equals' . md5(serialize([$fakePart])))
			->times(4);
		$part = new Subscribing\CachedPart(
			new Subscribing\FakePart($content, $equals),
			$this->cache
		);

		Assert::same($content, $part->content());
		Assert::same($content, $part->content());

		Assert::false($part->equals($fakePart));
		Assert::false($part->equals($fakePart));

		Assert::same($fakePart, $part->refresh());
		Assert::same($fakePart, $part->refresh());
	}
}

(new CachedPart())->run();
