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
		$source = new Subscribing\FakePage();
		$expression = new Subscribing\FakeExpression('//p', null);
		$visitedAt = new Subscribing\FakeInterval();
		$equals = false;
		$part = new Subscribing\FakePart();
		$this->cache->shouldReceive('read')
			->andReturn($content)
			->with('Remembrall\Model\Subscribing\CachedPart::content')
			->times(4);
		$this->cache->shouldReceive('read')
			->andReturn($source)
			->with('Remembrall\Model\Subscribing\CachedPart::source')
			->times(4);
		$this->cache->shouldReceive('read')
			->andReturn($expression)
			->with('Remembrall\Model\Subscribing\CachedPart::expression')
			->times(4);
		$this->cache->shouldReceive('read')
			->andReturn($visitedAt)
			->with('Remembrall\Model\Subscribing\CachedPart::visitedAt')
			->times(4);
		$this->cache->shouldReceive('read')
			->andReturn($equals)
			->with('Remembrall\Model\Subscribing\CachedPart::equals' . md5(serialize([$part])))
			->times(4);
		$page = new Subscribing\CachedPart(
			new Subscribing\FakePart(
				$source,
				$expression,
				$content,
				$equals,
				$visitedAt
			),
			$this->cache
		);

		Assert::same($content, $page->content());
		Assert::same($content, $page->content());

		Assert::same($source, $page->source());
		Assert::same($source, $page->source());

		Assert::same($expression, $page->expression());
		Assert::same($expression, $page->expression());

		Assert::same($visitedAt, $page->visitedAt());
		Assert::same($visitedAt, $page->visitedAt());

		Assert::false($page->equals($part));
		Assert::false($page->equals($part));
	}
}

(new CachedPart())->run();
