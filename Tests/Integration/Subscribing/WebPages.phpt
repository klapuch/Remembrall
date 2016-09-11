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

final class WebPages extends TestCase\Database {
	public function testAddindMultipleDifferentPages() {
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
		Assert::same(
			2,
			$this->database->fetchColumn(
				"SELECT COUNT(*)
				FROM page_visits
				WHERE visited_at <= NOW()"
			)
		);
	}

    public function testAddingSameUrlWithoutDuplication() {
        $this->database->query(
			"INSERT INTO pages (url, content) VALUES
			('www.facedown.cz', '<p>facedown</p>')"
		);
		$dom = new \DOMDocument();
        $dom->loadHTML('content');
        $page = new Subscribing\FakePage($dom);
		$addedPage = (new Subscribing\WebPages($this->database))
            ->add(new Uri\FakeUri('www.facedown.cz'), $page);
        Assert::same($addedPage, $page);
        Assert::count(1, $this->database->fetchAll('SELECT * FROM pages'));
	}

	protected function prepareDatabase() {
		$this->truncate(['pages', 'page_visits']);
		$this->restartSequence(['page_visits']);
    }
}

(new WebPages)->run();
