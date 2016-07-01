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
		$owner = new Access\FakeSubscriber(1, 'facedown@email.cz');
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
			->andReturn($owner)
			->with('Remembrall\Model\Subscribing\CachedPart::owner')
			->times(4);
		$page = new Subscribing\CachedPart(
			new Subscribing\FakePart(
				$content,
				$source,
				false,
				$expression,
				$owner
			),
			$this->cache
		);

		Assert::same($content, $page->content());
		Assert::same($content, $page->content());

		Assert::same($source, $page->source());
		Assert::same($source, $page->source());

		Assert::same($expression, $page->expression());
		Assert::same($expression, $page->expression());

		Assert::same($owner, $page->owner());
		Assert::same($owner, $page->owner());
	}
}

(new CachedPart())->run();
