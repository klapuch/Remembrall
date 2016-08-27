<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class WebPages extends TestCase\Database {
	public function testAdding() {
		$dom = new \DOMDocument();
		$dom->loadHTML('content');
		(new Subscribing\WebPages($this->database))
			->add('www.FacedowN.cz/', new Subscribing\FakePage($dom));
		(new Subscribing\WebPages($this->database))
			->add('www.FacedowN.cz/?x=10#here', new Subscribing\FakePage($dom));
		Assert::contains(
			'content',
			$this->database->fetchColumn(
				"SELECT content FROM pages WHERE url = 'www.facedown.cz'"
			)
		);
		Assert::same(
			2,
			$this->database->fetchColumn(
				"SELECT COUNT(*)
				FROM page_visits
                WHERE page_url = 'www.facedown.cz'
                OR page_url = 'www.facedown.cz/?x=10#here'
				AND visited_at <= NOW()"
			)
		);
	}

    public function testAddingSameUrl() {
		$dom = new \DOMDocument();
        $dom->loadHTML('content');
        $page = new Subscribing\FakePage($dom);
		$addedPage = (new Subscribing\WebPages($this->database))
            ->add('www.FacedowN.cz/', $page);
        Assert::same($addedPage, $page);
        Assert::count(
            1,
            $this->database->fetchAll(
                "SELECT *
                FROM pages
                WHERE url = 'www.facedown.cz'"
            )
        );
	}

	public function testIterating() {
		$this->database->query(
			"INSERT INTO pages (url, content) VALUES
			('www.facedown.cz', 'facedown')"
		);
		Assert::equal(
			[
				new Subscribing\ConstantPage(
					new Subscribing\FakePage(),
					'<p>google</p>'
				),
				new Subscribing\ConstantPage(
					new Subscribing\FakePage(),
					'facedown'
				),
			],
			(new Subscribing\WebPages($this->database))->iterate()
		);
	}

	public function testEmptyPages() {
		$this->truncate(['pages']);
		Assert::same(
			[],
			(new Subscribing\WebPages($this->database))->iterate()
		);
	}

	protected function prepareDatabase() {
		$this->truncate(['pages', 'page_visits']);
		$this->restartSequence(['page_visits']);
		$this->database->query(
			"INSERT INTO pages (url, content) VALUES
			('www.google.com', '<p>google</p>')"
		);
	}
}

(new WebPages)->run();
