<?php
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Subscribing;

use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class CachedPage extends TestCase\Mockery {
	/** @var \Mockery\Mock */
	private $cache;

	public function setUp() {
		parent::setUp();
		$this->cache = $this->mock('Nette\Caching\IStorage');
	}

	public function testCaching() {
		$content = new \DOMDocument();
		$content->loadHTML('<p>XXX</p>');
		$fakePage = new Subscribing\FakePage();
		$this->cache->shouldReceive('read')
			->andReturn($content)
			->with('Remembrall\Model\Subscribing\CachedPage::content#-')
			->times(4);
		$this->cache->shouldReceive('read')
			->andReturn($fakePage)
			->with('Remembrall\Model\Subscribing\CachedPage::refresh#-')
			->times(4);
		$part = new Subscribing\CachedPage(
			new Subscribing\FakePage($content, $fakePage),
			$this->cache
		);
		Assert::same($content, $part->content());
		Assert::same($content, $part->content());
		Assert::same($fakePage, $part->refresh());
		Assert::same($fakePage, $part->refresh());
	}
}

(new CachedPage())->run();