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
	public function testAddingBrandNew() {
		$url = new Uri\FakeUri('www.facedown.cz');
		$dom = new \DOMDocument();
		$dom->loadHTML('content');
		$page = new Subscribing\FakePage($dom);
		Assert::equal(
			new Subscribing\PostgresPage($page, $url, $this->database),
			(new Subscribing\WebPages(
				$this->database
			))->add($url, $page)
		);
		$statement = $this->database->prepare('SELECT * FROM pages');
		$statement->execute();
		$pages = $statement->fetchAll();
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
		$statement = $this->database->prepare(
			"SELECT *
			FROM page_visits
			WHERE visited_at >= NOW() - INTERVAL '1 MINUTE'"
		);
		$statement->execute();
		Assert::count(1, $statement->fetchAll());
	}

	public function testAddingToOthers() {
		$dom = new \DOMDocument();
		$dom->loadHTML('content');
		$this->database->query(
			"INSERT INTO pages (url, content) VALUES
			('www.facedown.cz', '<p>facedown</p>')"
		);
		(new Subscribing\WebPages($this->database))->add(
			new Uri\FakeUri('www.google.com'),
			new Subscribing\FakePage($dom)
		);
		$statement = $this->database->prepare('SELECT * FROM pages');
		$statement->execute();
		Assert::count(2, $statement->fetchAll());
	}

	public function testUpdatingAsDuplication() {
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
		$statement = $this->database->prepare('SELECT * FROM pages');
		$statement->execute();
		$pages = $statement->fetchAll();
		Assert::count(1, $pages);
		Assert::contains('content', $pages[0]['content']);
	}

	public function testUpdatingAsDuplicationWithRecordedVisitation() {
		$this->database->exec(
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
		$statement = $this->database->prepare('SELECT * FROM page_visits');
		$statement->execute();
		Assert::count(1, $statement->fetchAll());
	}

	protected function prepareDatabase() {
		$this->truncate(['pages', 'page_visits']);
	}
}

(new WebPages)->run();