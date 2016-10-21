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

final class WebPages extends TestCase\Database {
	public function testAddingSinglePage() {
		$url = new Uri\FakeUri('www.facedown.cz');
		$dom = new \DOMDocument();
		$dom->loadHTML('content');
		$page = new Subscribing\FakePage($dom);
		Assert::equal(
			new Subscribing\PostgresPage($page, $url, $this->database),
			(new Subscribing\WebPages($this->database))->add(
				$url,
				$page
			)
		);
		$pages = $this->database->fetchAll("SELECT * FROM pages");
		Assert::count(1, $pages);
		Assert::contains('content', $pages[0]['content']);
		Assert::same('www.facedown.cz', $pages[0]['url']);
	}

	public function testRecordingVisitation() {
		$this->truncate(['page_visits']);
		$dom = new \DOMDocument();
		$dom->loadHTML('content');
		(new Subscribing\WebPages($this->database))->add(
			new Uri\FakeUri('www.facedown.cz'),
			new Subscribing\FakePage($dom)
		);
		Assert::count(
			1,
			$this->database->fetchAll('SELECT * FROM page_visits')
		);
	}

	public function testAddingMultipleDifferentPages() {
		$dom = new \DOMDocument();
		$dom->loadHTML('content');
		(new Subscribing\WebPages($this->database))->add(
			new Uri\FakeUri('www.facedown.cz'),
			new Subscribing\FakePage($dom)
		);
		(new Subscribing\WebPages($this->database))->add(
			new Uri\FakeUri('www.google.com'),
			new Subscribing\FakePage($dom)
		);
		Assert::count(2, $this->database->fetchAll('SELECT * FROM pages'));
	}

	public function testUpdatingPageAsDuplication() {
		$this->database->query(
			"INSERT INTO pages (url, content) VALUES
			('www.facedown.cz', '<p>facedown</p>')"
		);
		$dom = new \DOMDocument();
		$dom->loadHTML('content');
		$url = new Uri\FakeUri('www.facedown.cz');
		$page = new Subscribing\FakePage($dom);
		$addedPage = (new Subscribing\WebPages(
			$this->database
		))->add($url, $page);
		Assert::equal(
			new Subscribing\PostgresPage($page, $url, $this->database),
			$addedPage
		);
		$pages = $this->database->fetchAll('SELECT * FROM pages');
		Assert::count(1, $pages);
		Assert::contains('content', $pages[0]['content']);
	}

	public function testUpdatingPageAsDuplicationWithRecordedVisitation() {
		$this->database->query(
			"INSERT INTO pages (url, content) VALUES
			('www.facedown.cz', '<p>facedown</p>')"
		);
		$this->truncate(['page_visits']);
		$dom = new \DOMDocument();
		$dom->loadHTML('content');
		$page = new Subscribing\FakePage($dom);
		(new Subscribing\WebPages(
			$this->database
		))->add(new Uri\FakeUri('www.facedown.cz'), $page);
		Assert::count(
			1,
			$this->database->fetchAll('SELECT * FROM page_visits')
		);
	}

	protected function prepareDatabase() {
		$this->truncate(['pages']);
	}
}

(new WebPages)->run();
