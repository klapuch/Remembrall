<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Http;

use Remembrall\Model\Http;
use Remembrall\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class CachingBrowser extends TestCase\Database {
	public function testCaching() {
		$headers = ['Status' => '200 OK'];
		$this->database->query(
			'INSERT INTO pages (url, content, headers) VALUES
			("google.com", "foo", ?)',
			serialize($headers)
		);
		$this->database->query(
			'INSERT INTO page_visits (page_id, visited_at) VALUES (1, NOW())'
		);
		Assert::equal(
			new Http\ConstantResponse(
				new Http\UniqueHeaders($headers),
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

	public function testExpiredCachingByEmptyHeaders() {
		$this->database->query(
			'INSERT INTO pages (url, content, headers) VALUES
			("google.com", "foo", "")'
		);
		$this->database->query(
			'INSERT INTO page_visits (page_id, visited_at) VALUES
			(1, NOW())'
		);
		$headers = ['Status' => '200 OK'];
		$response = new Http\FakeResponse(
			new Http\FakeHeaders($headers), 'bar'
		);
		Assert::equal(
			$response,
			(new Http\CachingBrowser(new Http\FakeBrowser($response), $this->database))
				->send(
					new Http\ConstantRequest(
						new Http\FakeHeaders(['host' => 'google.com'])
					)
				)
		);
		$pages = $this->database->fetchAll('SELECT * FROM pages');
		Assert::count(1, $pages);
		Assert::same(serialize($headers), $pages[0]['headers']);
		Assert::same('google.com', $pages[0]['url']);
		Assert::same('bar', $pages[0]['content']);
		$visits = $this->database->fetchAll('SELECT * FROM page_visits');
		Assert::count(2, $visits);
		Assert::same(1, $visits[0]['page_id']);
		Assert::same(1, $visits[1]['page_id']);
	}

	public function testExpiredCachingByOldVisitation() {
		$this->database->query(
			'INSERT INTO pages (url, content, headers) VALUES
			("google.com", "foo", "not empty")'
		);
		$this->database->query(
			'INSERT INTO page_visits (page_id, visited_at) VALUES
			(1, NOW() - INTERVAL 11 MINUTE)'
		);
		$headers = ['Status' => '200 OK'];
		$response = new Http\FakeResponse(
			new Http\FakeHeaders($headers), 'bar'
		);
		Assert::equal(
			$response,
			(new Http\CachingBrowser(new Http\FakeBrowser($response), $this->database))
				->send(
					new Http\ConstantRequest(
						new Http\FakeHeaders(['host' => 'google.com'])
					)
				)
		);
		$pages = $this->database->fetchAll('SELECT * FROM pages');
		Assert::count(1, $pages);
		Assert::same(serialize($headers), $pages[0]['headers']);
		Assert::same('google.com', $pages[0]['url']);
		Assert::same('bar', $pages[0]['content']);
		$visits = $this->database->fetchAll('SELECT * FROM page_visits');
		Assert::count(2, $visits);
		Assert::same(1, $visits[0]['page_id']);
		Assert::same(1, $visits[1]['page_id']);
	}

	protected function prepareDatabase() {
		$this->database->query('TRUNCATE pages');
		$this->database->query('TRUNCATE page_visits');
	}
}

(new CachingBrowser())->run();
