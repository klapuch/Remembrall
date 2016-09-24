<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Subscribing;

use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;
use Klapuch\Output;

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
		$fakePart = new Subscribing\FakePart();
		$this->cache->shouldReceive('read')
			->andReturn($content)
			->with('Remembrall\Model\Subscribing\CachedPart::content#-')
			->times(4);
		$this->cache->shouldReceive('read')
			->andReturn($fakePart)
			->with('Remembrall\Model\Subscribing\CachedPart::refresh#-')
			->times(4);
		$part = new Subscribing\CachedPart(
			new Subscribing\FakePart($content, $fakePart),
			$this->cache
		);

		Assert::same($content, $part->content());
		Assert::same($content, $part->content());

		Assert::same($fakePart, $part->refresh());
		Assert::same($fakePart, $part->refresh());
	}
}

(new CachedPart())->run();
