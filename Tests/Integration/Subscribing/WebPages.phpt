<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class WebPages extends TestCase\Database {
	public function testAdding() {
		$dom = new \DOMDocument();
		$dom->loadHTML('content');
		(new Subscribing\WebPages($this->database))
			->add('www.FacedowN.cz/', new Subscribing\FakePage($dom));
		(new Subscribing\WebPages($this->database))
			->add('www.FacedowN.cz/?x=10#here', new Subscribing\FakePage($dom));
		Assert::contains(
			'content',
			$this->database->fetchSingle(
				'SELECT content FROM pages WHERE url = "www.facedown.cz"'
			)
		);
		Assert::same(
			2,
			$this->database->fetchSingle(
				'SELECT COUNT(*)
				FROM page_visits
				WHERE page_url = "www.facedown.cz" OR page_url = "www.facedown.cz/?x=10#here"
				AND visited_at <= NOW()'
			)
		);
	}

	public function testAddingSameUrl() {
		$dom = new \DOMDocument();
		$dom->loadHTML('content');
		(new Subscribing\WebPages($this->database))
			->add('www.FacedowN.cz/', new Subscribing\FakePage($dom));
		$dom2 = new \DOMDocument();
		$dom2->loadHTML('Updated Content');
		(new Subscribing\WebPages($this->database))
			->add('www.facedown.cz/', new Subscribing\FakePage($dom2));
		$pages = $this->database->fetchAll(
			'SELECT * FROM pages WHERE url = "www.facedown.cz"'
		);
		Assert::count(1, $pages);
		Assert::contains('Updated Content', $pages[0]['content']);
		Assert::same(
			2,
			$this->database->fetchSingle(
				'SELECT COUNT(*)
				FROM page_visits
				WHERE page_url = "www.facedown.cz"
				AND visited_at <= NOW()'
			)
		);
	}

	public function testIterating() {
		$this->database->query(
			'INSERT INTO pages (url, content) VALUES
			("www.facedown.cz", "facedown")'
		);
		Assert::equal(
			[
				new Subscribing\ConstantPage('<p>google</p>', 'www.google.com'),
				new Subscribing\ConstantPage('facedown', 'www.facedown.cz'),
			],
			(new Subscribing\WebPages($this->database))->iterate()
		);
	}

	protected function prepareDatabase() {
		$this->truncate(['pages', 'page_visits']);
		$this->restartSequence(['page_visits']);
		$this->database->query(
			'INSERT INTO pages (url, content) VALUES
			("www.google.com", "<p>google</p>")'
		);
	}
}

(new WebPages)->run();
