<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;
use Klapuch\Uri;

require __DIR__ . '/../../bootstrap.php';

final class PostgresPage extends TestCase\Database {
    public function testHtmlContent() {
		Assert::contains(
			'facedown content',
			(new Subscribing\PostgresPage(
				new Subscribing\FakePage(),
				new Uri\FakeUri('www.facedown.cz'),
				$this->database
			))->content()->saveHTML()
		);
	}

    public function testRefreshingWithNewContent() {
        $content = new \DOMDocument();
        $content->loadHTML('NEW_CONTENT');
		(new Subscribing\PostgresPage(
            new Subscribing\FakePage(
                new \DOMDocument(),
                new Subscribing\FakePage($content)
            ),
			new Uri\FakeUri('www.facedown.cz'),
			$this->database
		))->refresh();
		$page = $this->database->fetch(
			"SELECT * FROM pages WHERE url = 'www.facedown.cz'"
		);
		Assert::contains('NEW_CONTENT', $page['content']);
	}

	public function testRefreshingWithoutAffectingOthers() {
        $content = new \DOMDocument();
        $content->loadHTML('NEW_CONTENT');
		(new Subscribing\PostgresPage(
            new Subscribing\FakePage(
                new \DOMDocument(),
                new Subscribing\FakePage($content)
            ),
			new Uri\FakeUri('www.facedown.cz'),
			$this->database
		))->refresh();
		$pages = $this->database->fetchAll('SELECT * FROM pages');
		Assert::count(2, $pages);
		Assert::contains('google content', $pages[0]['content']);
		Assert::contains('NEW_CONTENT', $pages[1]['content']);
	}

	public function testRecordingVisitation() {
		$this->truncate(['page_visits']);
        $content = new \DOMDocument();
        $content->loadHTML('NEW_CONTENT');
		(new Subscribing\PostgresPage(
            new Subscribing\FakePage(
                new \DOMDocument(),
                new Subscribing\FakePage($content)
            ),
			new Uri\FakeUri('www.facedown.cz'),
			$this->database
		))->refresh();
		$pages = $this->database->fetchAll('SELECT * FROM page_visits');
		Assert::count(1, $pages);
	}

    protected function prepareDatabase() {
        $this->truncate(['pages']);
		$this->database->query(
			"INSERT INTO pages (url, content) VALUES
			('www.facedown.cz', 'facedown content'),
			('www.google.com', 'google content')"
		);
	}
}

(new PostgresPage)->run();
