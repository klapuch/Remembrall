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

final class FrugalRequest extends TestCase\Database {
	public function testCachedRequest() {
		$this->database->query(
			'INSERT INTO page_visits (page_url, visited_at) VALUES
			("www.google.com", NOW())'
		);
		Assert::equal(
			new Subscribing\ConstantPage('google'),
			(new Http\FrugalRequest(
				new Http\FakeRequest(),
				'www.google.com',
				new Subscribing\FakePages(),
				$this->database
			))->send()
		);
	}

	public function testCachedRequestWithMultipleVisitation() {
		$this->database->query(
			'INSERT INTO page_visits (page_url, visited_at) VALUES
			("www.google.com", NOW() - INTERVAL 70 MINUTE),
			("www.google.com", NOW()),
			("www.google.com", NOW() - INTERVAL 20 MINUTE)'
		);
		Assert::equal(
			new Subscribing\ConstantPage('google'),
			(new Http\FrugalRequest(
				new Http\FakeRequest(),
				'www.google.com',
				new Subscribing\FakePages(),
				$this->database
			))->send()
		);
	}

	public function testExpiredCaching() {
		$this->database->query(
			'INSERT INTO page_visits (page_url, visited_at) VALUES
			("www.google.com", NOW() - INTERVAL 11 MINUTE)'
		);
		$page = new Subscribing\FakePage(new \DOMDocument());
		Assert::same(
			$page,
			(new Http\FrugalRequest(
				new Http\FakeRequest($page),
				'www.google.com',
				new Subscribing\FakePages(),
				$this->database
			))->send()
		);
	}

	public function testExpiredCachingWithMultipleVisitation() {
		$this->database->query(
			'INSERT INTO page_visits (page_url, visited_at) VALUES
			("www.google.com", NOW() - INTERVAL 11 MINUTE),
			("www.google.com", NOW() - INTERVAL 20 MINUTE),
			("www.google.com", NOW() - INTERVAL 70 MINUTE)'
		);
		$page = new Subscribing\FakePage(new \DOMDocument());
		Assert::same(
			$page,
			(new Http\FrugalRequest(
				new Http\FakeRequest($page),
				'www.google.com',
				new Subscribing\FakePages(),
				$this->database
			))->send()
		);
	}

	public function testExpiredCachingBecauseOfFirstVisit() {
		$page = new Subscribing\FakePage(new \DOMDocument());
		Assert::same(
			$page,
			(new Http\FrugalRequest(
				new Http\FakeRequest($page),
				'www.google.com',
				new Subscribing\FakePages(),
				$this->database
			))->send()
		);
	}

	protected function prepareDatabase() {
		$this->database->query('TRUNCATE pages');
		$this->database->query(
			'INSERT INTO pages (url, content) VALUES
			("www.google.com", "google")'
		);
		$this->database->query('TRUNCATE page_visits');
	}
}

(new FrugalRequest())->run();