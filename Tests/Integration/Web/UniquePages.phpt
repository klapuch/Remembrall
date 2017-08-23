<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Web;

use Klapuch\Uri;
use Remembrall\Misc;
use Remembrall\Model\Web;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class UniquePages extends \Tester\TestCase {
	use TestCase\Database;

	public function testAddingBrandNewOne() {
		$url = new Uri\FakeUri('www.facedown.cz');
		$dom = new \DOMDocument();
		$dom->loadHTML('content');
		$page = new Web\FakePage($dom);
		Assert::equal(
			new Web\StoredPage($page, $url, $this->database),
			(new Web\UniquePages($this->database))->add($url, $page)
		);
		(new Misc\TableCount($this->database, 'pages', 1))->assert();
		$page = $this->database->query('SELECT * FROM pages')->fetch();
		Assert::contains('content', $page['content']);
		Assert::same('www.facedown.cz', $page['url']);
	}

	public function testRecordingVisitation() {
		$this->truncate(['page_visits']);
		$dom = new \DOMDocument();
		$dom->loadHTML('content');
		(new Web\UniquePages($this->database))->add(
			new Uri\FakeUri('www.facedown.cz'),
			new Web\FakePage($dom)
		);
		(new Misc\TableCount($this->database, 'page_visits', 1))->assert();
	}

	public function testAddingToOthers() {
		$dom = new \DOMDocument();
		$dom->loadHTML('content');
		$pages = new Web\UniquePages($this->database);
		$pages->add(new Uri\FakeUri('www.google.com'), new Web\FakePage($dom));
		$pages->add(new Uri\FakeUri('www.seznam.cz'), new Web\FakePage($dom));
		(new Misc\TableCount($this->database, 'pages', 2))->assert();
		$pages = $this->database->query('SELECT * FROM pages')->fetchAll();
		Assert::notSame($pages[0], $pages[1]);
	}

	public function testUpdatingContentOnDuplication() {
		$oldDom = new \DOMDocument();
		$oldDom->loadHTML('old content');
		$newDom = new \DOMDocument();
		$newDom->loadHTML('new content');
		$url = new Uri\FakeUri('www.facedown.cz');
		$pages = new Web\UniquePages($this->database);
		$pages->add($url, new Web\FakePage($oldDom));
		$pages->add($url, new Web\FakePage($newDom));
		(new Misc\TableCount($this->database, 'pages', 1))->assert();
		Assert::contains('new content', $this->database->query('SELECT content FROM pages')->fetchColumn());
	}

	public function testUpdatingOnDuplicationWithRecordedVisitation() {
		$oldDom = new \DOMDocument();
		$oldDom->loadHTML('old content');
		$newDom = new \DOMDocument();
		$newDom->loadHTML('new content');
		$url = new Uri\FakeUri('www.facedown.cz');
		$pages = new Web\UniquePages($this->database);
		$pages->add($url, new Web\FakePage($oldDom));
		$this->truncate(['page_visits']);
		$pages->add($url, new Web\FakePage($newDom));
		(new Misc\TableCount($this->database, 'page_visits', 1))->assert();
	}
}

(new UniquePages)->run();