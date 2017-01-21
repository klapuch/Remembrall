<?php
declare(strict_types = 1);
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

final class UniquePages extends TestCase\Database {
	public function testAddingBrandNewOne() {
		$url = new Uri\FakeUri('www.facedown.cz');
		$dom = new \DOMDocument();
		$dom->loadHTML('content');
		$page = new Subscribing\FakePage($dom);
		Assert::equal(
			new Subscribing\StoredPage($page, $url, $this->database),
			(new Subscribing\UniquePages($this->database))->add($url, $page)
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
		(new Subscribing\UniquePages($this->database))->add(
			new Uri\FakeUri('www.facedown.cz'),
			new Subscribing\FakePage($dom)
		);
		$statement = $this->database->prepare('SELECT * FROM page_visits');
		$statement->execute();
		Assert::count(1, $statement->fetchAll());
	}

	public function testAddingToOthers() {
		$dom = new \DOMDocument();
		$dom->loadHTML('content');
		$pages = new Subscribing\UniquePages($this->database);
		$pages->add(new Uri\FakeUri('www.google.com'), new Subscribing\FakePage($dom));
		$pages->add(new Uri\FakeUri('www.seznam.cz'), new Subscribing\FakePage($dom));
		$statement = $this->database->prepare('SELECT * FROM pages');
		$statement->execute();
		$pages = $statement->fetchAll();
		Assert::count(2, $pages);
		Assert::notSame($pages[0], $pages[1]);
	}

	public function testUpdatingContentOnDuplication() {
		$oldDom = new \DOMDocument();
		$oldDom->loadHTML('old content');
		$newDom = new \DOMDocument();
		$newDom->loadHTML('new content');
		$url = new Uri\FakeUri('www.facedown.cz');
		$pages = new Subscribing\UniquePages($this->database);
		$pages->add($url, new Subscribing\FakePage($oldDom));
		$pages->add($url, new Subscribing\FakePage($newDom));
		$statement = $this->database->prepare('SELECT * FROM pages');
		$statement->execute();
		$pages = $statement->fetchAll();
		Assert::count(1, $pages);
		Assert::contains('new content', $pages[0]['content']);
	}

	public function testUpdatingOnDuplicationWithRecordedVisitation() {
		$oldDom = new \DOMDocument();
		$oldDom->loadHTML('old content');
		$newDom = new \DOMDocument();
		$newDom->loadHTML('new content');
		$url = new Uri\FakeUri('www.facedown.cz');
		$pages = new Subscribing\UniquePages($this->database);
		$pages->add($url, new Subscribing\FakePage($oldDom));
		$this->truncate(['page_visits']);
		$pages->add($url, new Subscribing\FakePage($newDom));
		$statement = $this->database->prepare('SELECT * FROM page_visits');
		$statement->execute();
		Assert::count(1, $statement->fetchAll());
	}

	protected function prepareDatabase() {
		$this->truncate(['pages', 'page_visits']);
	}
}

(new UniquePages)->run();