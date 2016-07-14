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
		$this->database->query(
			'INSERT INTO page_visits (page_url, visited_at) VALUES
			("www.google.com", NOW())'
		);
		Assert::equal(
			new Subscribing\ConstantPage('www.google.com', 'google'),
			(new Http\CachingBrowser(new Http\FakeBrowser(), $this->database))
				->send(
					new Http\ConstantRequest(
						new Http\FakeHeaders(['host' => 'www.google.com'])
					)
				)
		);
	}

	public function testExpiredCachingByOldVisitation() {
		$this->database->query(
			'INSERT INTO page_visits (page_url, visited_at) VALUES
			("www.google.com", NOW() - INTERVAL 11 MINUTE)'
		);
		$page = new Subscribing\FakePage('whatever.com', new \DOMDocument());
		Assert::equal(
			$page,
			(new Http\CachingBrowser(new Http\FakeBrowser($page), $this->database))
				->send(
					new Http\ConstantRequest(
						new Http\FakeHeaders(['host' => 'www.google.com'])
					)
				)
		);
	}

	protected function prepareDatabase() {
		$this->database->query('TRUNCATE pages');
		$this->database->query('TRUNCATE page_visits');
		$this->database->query(
			'INSERT INTO pages (url, content) VALUES
			("www.google.com", "google")'
		);
	}
}

(new CachingBrowser())->run();
