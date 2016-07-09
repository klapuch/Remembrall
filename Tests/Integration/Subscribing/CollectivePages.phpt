<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use Dibi;
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;
use Nette\Security;

require __DIR__ . '/../../bootstrap.php';

final class CollectivePages extends TestCase\Database {
	public function testAddingBrandNew() {
		$dom = new \DOMDocument();
		$dom->loadHTML('<p>Content</p>');
		(new Subscribing\CollectivePages($this->database))->add(
			new Subscribing\FakePage('www.google.com', $dom)
		);
		$pages = $this->database->fetchAll('SELECT * FROM pages');
		Assert::count(1, $pages);
		$page = current($pages);
		Assert::same('www.google.com', $page['url']);
		Assert::contains('<p>Content</p>', $page['content']);
		$visits = $this->database->fetchAll('SELECT * FROM page_visits');
		Assert::count(1, $visits);
		Assert::same(1, $visits[0]['page_id']);
	}

	public function testAddingWithRewritingContent() {
		$this->database->query(
			'INSERT INTO pages (url, content) VALUES
			("www.google.com", "<p>Content</p>")'
		);
		$dom = new \DOMDocument();
		$dom->loadHTML('<p>The content is rewritten with this</p>');
		(new Subscribing\CollectivePages($this->database))->add(
			new Subscribing\FakePage('www.google.com', $dom)
		);
		$pages = $this->database->fetchAll('SELECT * FROM pages');
		Assert::count(1, $pages);
		$page = current($pages);
		Assert::same('www.google.com', $page['url']);
		Assert::contains(
			'<p>The content is rewritten with this</p>',
			$page['content']
		);
		$visits = $this->database->fetchAll('SELECT * FROM page_visits');
		Assert::count(1, $visits);
		Assert::same(1, $visits[0]['page_id']);
	}

	public function testIterating() {
		$this->database->query(
			'INSERT INTO pages (url, content) VALUES
			("www.google.com", "<p>Content</p>"),
			("www.seznam.cz", "<p>XXX</p>")'
		);
		$pages = (new Subscribing\CollectivePages($this->database))->iterate();
		Assert::count(2, $pages);
		Assert::same('www.google.com', $pages[0]->url());
		Assert::contains('<p>Content</p>', $pages[0]->content()->saveHTML());
		Assert::same('www.seznam.cz', $pages[1]->url());
		Assert::contains('<p>XXX</p>', $pages[1]->content()->saveHTML());
	}

	public function testReplacingContentWithoutUrlChange() {
		$this->database->query(
			'INSERT INTO pages (url, content) VALUES
			("www.google.com", "<p>Content</p>")'
		);
		$dom = new \DOMDocument();
		$dom->loadHTML('<p>google.com new content</p>');
		(new Subscribing\CollectivePages($this->database))
			->replace(
				new Subscribing\FakePage('www.google.com'),
				new Subscribing\FakePage('www.whatever.com', $dom)
			);
		$pages = $this->database->fetchAll('SELECT * FROM pages');
		Assert::count(1, $pages);
		Assert::contains('<p>google.com new content</p>', $pages[0]['content']);
		Assert::same('www.google.com', $pages[0]['url']);
		$visits = $this->database->fetchAll('SELECT * FROM page_visits');
		Assert::count(1, $visits);
		Assert::same(1, $visits[0]['page_id']);
	}

    protected function prepareDatabase() {
		$this->database->query('TRUNCATE pages');
		$this->database->query('TRUNCATE page_visits');
    }
}

(new CollectivePages)->run();
