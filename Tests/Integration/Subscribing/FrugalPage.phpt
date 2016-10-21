<?php
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Subscribing;

use Klapuch\Uri;
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class FrugalPage extends TestCase\Database {
	public function testFrugalPage() {
		$this->database->query(
			"INSERT INTO page_visits (page_url, visited_at) VALUES
			('www.google.com', NOW())"
		);
		Assert::contains(
			'google',
			(new Subscribing\FrugalPage(
				new Uri\FakeUri('www.google.com'),
				new Subscribing\FakePage(),
				$this->database
			))->content()->saveHTML()
		);
	}

	public function testFrugalPageWithMultipleVisitation() {
		$this->database->query(
			"INSERT INTO page_visits (page_url, visited_at) VALUES
			('www.google.com', NOW() - INTERVAL '70 MINUTE'),
			('www.google.com', NOW()),
			('www.google.com', NOW() - INTERVAL '20 MINUTE')"
		);
		Assert::contains(
			'google',
			(new Subscribing\FrugalPage(
				new Uri\FakeUri('www.google.com'),
				new Subscribing\FakePage(),
				$this->database
			))->content()->saveHTML()
		);
	}

	public function testOutdatedPage() {
		$this->database->query(
			"INSERT INTO page_visits (page_url, visited_at) VALUES
			('www.google.com', NOW() - INTERVAL '11 MINUTE')"
		);
		$dom = new \DOMDocument();
		$dom->loadHTML('<p>Google</p>');
		Assert::contains(
			'<p>Google</p>',
			(new Subscribing\FrugalPage(
				new Uri\FakeUri('www.google.com'),
				new Subscribing\FakePage(
					new \DOMDocument(),
					new Subscribing\FakePage($dom)
				),
				$this->database
			))->content()->saveHTML()
		);
	}

	public function testOutdatedPageWithMultipleVisitation() {
		$this->database->query(
			"INSERT INTO page_visits (page_url, visited_at) VALUES
			('www.google.com', NOW() - INTERVAL '11 MINUTE'),
			('www.google.com', NOW() - INTERVAL '20 MINUTE'),
			('www.google.com', NOW() - INTERVAL '70 MINUTE')"
		);
		$dom = new \DOMDocument();
		$dom->loadHTML('<p>Google</p>');
		Assert::contains(
			'<p>Google</p>',
			(new Subscribing\FrugalPage(
				new Uri\FakeUri('www.google.com'),
				new Subscribing\FakePage(
					new \DOMDocument(),
					new Subscribing\FakePage($dom)
				),
				$this->database
			))->content()->saveHTML()
		);
	}

	public function testOriginContentAsFirstVisit() {
		$this->truncate(['pages']);
		$dom = new \DOMDocument();
		$dom->loadHTML('<p>Google</p>');
		Assert::contains(
			'<p>Google</p>',
			(new Subscribing\FrugalPage(
				new Uri\FakeUri('www.google.com'),
				new Subscribing\FakePage(
					$dom,
					new Subscribing\FakePage(new \DOMDocument())
				),
				$this->database
			))->content()->saveHTML()
		);
	}

	protected function prepareDatabase() {
		$this->truncate(['pages']);
		$this->database->query(
			"INSERT INTO pages (url, content) VALUES
			('www.google.com', 'google')"
		);
		$this->purge(['page_visits']);
	}
}

(new FrugalPage())->run();
