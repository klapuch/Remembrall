<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
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
		$this->cache = $this->mockery('Nette\Caching\IStorage');
	}

	public function testCaching() {
		$dom = new \DOMDocument;
		$dom->loadHTML('<p>Paragraph</p>');
		$refreshedPage = new Subscribing\FakePage();
		$this->cache->shouldReceive('read')
			->andReturn($dom)
			->with('Remembrall\Model\Subscribing\CachedPage::content')
			->times(4);
		$this->cache->shouldReceive('read')
			->andReturn($refreshedPage)
			->with('Remembrall\Model\Subscribing\CachedPage::refresh')
			->times(4);
		$page = new Subscribing\CachedPage(
			new Subscribing\FakePage($dom),
			$this->cache
		);

		Assert::same($dom, $page->content());
		Assert::same($dom, $page->content());

		Assert::same($refreshedPage, $page->refresh());
		Assert::same($refreshedPage, $page->refresh());
	}
}

(new CachedPage())->run();
