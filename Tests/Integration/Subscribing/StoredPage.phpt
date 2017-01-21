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

final class StoredPage extends TestCase\Database {
	public function testHtmlContent() {
		Assert::contains(
			'facedown content',
			(new Subscribing\StoredPage(
				new Subscribing\FakePage(),
				new Uri\FakeUri('www.facedown.cz'),
				$this->database
			))->content()->saveHTML()
		);
	}

	public function testRefreshingToNewContent() {
		$url = 'www.facedown.cz';
		$content = new \DOMDocument();
		$content->loadHTML('NEW_CONTENT');
		(new Subscribing\StoredPage(
			new Subscribing\FakePage(
				new \DOMDocument(),
				new Subscribing\FakePage($content)
			),
			new Uri\FakeUri($url),
			$this->database
		))->refresh();
		$statement = $this->database->prepare('SELECT * FROM pages WHERE url = ?');
		$statement->execute([$url]);
		Assert::contains('NEW_CONTENT', $statement->fetch()['content']);
	}

	public function testRefreshingWithoutAffectingOthers() {
		$content = new \DOMDocument();
		$content->loadHTML('NEW_CONTENT');
		(new Subscribing\StoredPage(
			new Subscribing\FakePage(
				new \DOMDocument(),
				new Subscribing\FakePage($content)
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
		$this->truncate(['page_visits']);
		$content = new \DOMDocument();
		$content->loadHTML('NEW_CONTENT');
		(new Subscribing\StoredPage(
			new Subscribing\FakePage(
				new \DOMDocument(),
				new Subscribing\FakePage($content)
			),
			new Uri\FakeUri('www.facedown.cz'),
			$this->database
		))->refresh();
		$statement = $this->database->prepare('SELECT * FROM page_visits');
		$statement->execute();
		Assert::count(1, $statement->fetchAll());
	}

	protected function prepareDatabase() {
		$this->truncate(['pages']);
		$this->database->exec(
			"INSERT INTO pages (url, content) VALUES
			('www.facedown.cz', 'facedown content'),
			('www.google.com', 'google content')"
		);
	}
}

(new StoredPage)->run();