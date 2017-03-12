<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Web;

use Klapuch\Uri;
use Remembrall\Model\Web;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class StoredPage extends TestCase\Database {
	public function testHtmlContent() {
		$this->database->exec(
			"INSERT INTO pages (url, content) VALUES
			('www.facedown.cz', 'facedown content'),
			('www.google.com', 'google content')"
		);
		Assert::contains(
			'facedown content',
			(new Web\StoredPage(
				new Web\FakePage(),
				new Uri\FakeUri('www.facedown.cz'),
				$this->database
			))->content()->saveHTML()
		);
	}

	public function testRefreshingToNewContent() {
		$this->database->exec(
			"INSERT INTO pages (url, content) VALUES
			('www.facedown.cz', 'facedown content'),
			('www.google.com', 'google content')"
		);
		$url = 'www.facedown.cz';
		$content = new \DOMDocument();
		$content->loadHTML('NEW_CONTENT');
		(new Web\StoredPage(
			new Web\FakePage(
				new \DOMDocument(),
				new Web\FakePage($content)
			),
			new Uri\FakeUri($url),
			$this->database
		))->refresh();
		$statement = $this->database->prepare('SELECT * FROM pages WHERE url = ?');
		$statement->execute([$url]);
		Assert::contains('NEW_CONTENT', $statement->fetch()['content']);
	}

	public function testRefreshingWithoutAffectingOthers() {
		$this->database->exec(
			"INSERT INTO pages (url, content) VALUES
			('www.facedown.cz', 'facedown content'),
			('www.google.com', 'google content')"
		);
		$content = new \DOMDocument();
		$content->loadHTML('NEW_CONTENT');
		(new Web\StoredPage(
			new Web\FakePage(
				new \DOMDocument(),
				new Web\FakePage($content)
			),
			new Uri\FakeUri('www.facedown.cz'),
			$this->database
		))->refresh();
		$statement = $this->database->prepare('SELECT * FROM pages');
		$statement->execute();
		$pages = $statement->fetchAll();
		Assert::count(2, $pages);
		Assert::contains('google content', $pages[0]['content']);
		Assert::contains('NEW_CONTENT', $pages[1]['content']);
	}

	public function testRecordingVisitation() {
		$this->database->exec(
			"INSERT INTO pages (url, content) VALUES
			('www.facedown.cz', 'facedown content'),
			('www.google.com', 'google content')"
		);
		$this->truncate(['page_visits']);
		$content = new \DOMDocument();
		$content->loadHTML('NEW_CONTENT');
		(new Web\StoredPage(
			new Web\FakePage(
				new \DOMDocument(),
				new Web\FakePage($content)
			),
			new Uri\FakeUri('www.facedown.cz'),
			$this->database
		))->refresh();
		$statement = $this->database->prepare('SELECT * FROM page_visits');
		$statement->execute();
		Assert::count(1, $statement->fetchAll());
	}

	protected function prepareDatabase(): void {
		$this->truncate(['pages', 'page_visits']);
	}
}

(new StoredPage)->run();