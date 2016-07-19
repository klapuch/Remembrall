<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Subscribing;

use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester;
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
		$this->cache->shouldReceive('read')
			->andReturn($dom)
			->with('Remembrall\Model\Subscribing\CachedPage::content')
			->times(4);
		$page = new Subscribing\CachedPage(
			new Subscribing\FakePage($dom),
			$this->cache
		);

		Assert::same($dom, $page->content());
		Assert::same($dom, $page->content());
	}
}

(new CachedPage())->run();
