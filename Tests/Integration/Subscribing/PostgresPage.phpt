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
    public function testContent() {
		Assert::contains(
			'Hello from facedown website',
			(new Subscribing\PostgresPage(
				new Subscribing\FakePage(),
				new Uri\FakeUri('www.facedown.cz'),
				$this->database
			))->content()->saveHTML()
		);
	}

    public function testRefreshingPage() {
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
		Assert::count(1, $pages);
		Assert::contains('NEW_CONTENT', $pages[0]['content']);
	}

    protected function prepareDatabase() {
        $this->truncate(['pages']);
		$this->database->query(
			"INSERT INTO pages (url, content) VALUES
			('www.facedown.cz', 'Hello from facedown website')"
		);
	}
}

(new PostgresPage)->run();
