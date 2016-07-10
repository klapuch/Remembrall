<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Http;

use Remembrall\Model\{
	Http, Subscribing
};
use Remembrall\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class CachingBrowser extends TestCase\Database {
	public function testCaching() {
		$headers = ['Status' => '200 OK'];
		$this->database->query(
			'INSERT INTO pages (url, content) VALUES
			("google.com", "foo")'
		);
		$this->database->query(
			'INSERT INTO page_visits (page_id, visited_at) VALUES (1, NOW())'
		);
		Assert::equal(
			new Subscribing\ConstantPage(
				'google.com',
				'foo'
			),
			(new Http\CachingBrowser(new Http\FakeBrowser(), $this->database))
				->send(
					new Http\ConstantRequest(
						new Http\FakeHeaders(['host' => 'google.com'])
					)
				)
		);
	}

	public function testExpiredCachingByOldVisitation() {
		$this->database->query(
			'INSERT INTO pages (url, content) VALUES
			("google.com", "foo")'
		);
		$this->database->query(
			'INSERT INTO page_visits (page_id, visited_at) VALUES
			(1, NOW() - INTERVAL 11 MINUTE)'
		);
		$page = new Subscribing\FakePage(
			'whatever.com',
			new \DOMDocument()
		);
		Assert::equal(
			$page,
			(new Http\CachingBrowser(new Http\FakeBrowser($page), $this->database))
				->send(
					new Http\ConstantRequest(
						new Http\FakeHeaders(['host' => 'google.com'])
					)
				)
		);
	}

	protected function prepareDatabase() {
		$this->database->query('TRUNCATE pages');
		$this->database->query('TRUNCATE page_visits');
	}
}

(new CachingBrowser())->run();
