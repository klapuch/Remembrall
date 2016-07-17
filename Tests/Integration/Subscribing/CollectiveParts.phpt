<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use Dibi;
use Remembrall\Model\{
	Subscribing, Access, Http
};
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class CollectiveParts extends TestCase\Database {
    public function testSubscribing() {
		$this->database->query(
			'INSERT INTO subscribers (ID, email, `password`) VALUES
			(1, "foo@bar.cz", "secret"), (2, "facedown@facedown.cz", "secret")'
		);
        (new Subscribing\CollectiveParts(
            $this->database,
			new Http\FakeBrowser()
		))->subscribe(
			new Subscribing\FakePart('<p>Content</p>'),
			'www.google.com',
			'//p',
			new Subscribing\FakeInterval(
				new \DateTimeImmutable('2000-01-01 01:01:01')
			)
		);
		$parts = $this->database->fetchAll(
			'SELECT ID, page_url, content, expression FROM parts'
		);
		Assert::count(1, $parts);
		Assert::same(1, $parts[0]['ID']);
		Assert::same('www.google.com', $parts[0]['page_url']);
		Assert::same('<p>Content</p>', $parts[0]['content']);
		Assert::same('//p', $parts[0]['expression']);
		$partVisits = $this->database->fetchAll(
			'SELECT part_id FROM part_visits'
		);
		Assert::count(1, $partVisits);
    }

	public function testTwiceSubscribingWithUpdate() {
		$this->database->query(
			'INSERT INTO subscribers (ID, email, `password`) VALUES
			(1, "foo@bar.cz", "secret"), (2, "facedown@facedown.cz", "secret")'
		);
		(new Subscribing\CollectiveParts(
			$this->database,
			new Http\FakeBrowser()
		))->subscribe(
			new Subscribing\FakePart('<p>Content</p>'),
			'www.google.com',
			'//p',
			new Subscribing\FakeInterval(
				new \DateTimeImmutable('2000-01-01 01:01:01')
			)
		); //once
		(new Subscribing\CollectiveParts(
			$this->database,
			new Http\FakeBrowser()
		))->subscribe(
			new Subscribing\FakePart('<p>Updated content</p>'),
			'www.google.com',
			'//p',
			new Subscribing\FakeInterval(
				new \DateTimeImmutable('2000-01-01 01:01:01')
			)
		); //twice
		$parts = $this->database->fetchAll(
			'SELECT ID, page_url, content, expression FROM parts'
		);
		Assert::count(1, $parts);
		Assert::same(1, $parts[0]['ID']);
		Assert::same('www.google.com', $parts[0]['page_url']);
		Assert::same('<p>Updated content</p>', $parts[0]['content']);
		Assert::same('//p', $parts[0]['expression']);
		$partVisits = $this->database->fetchAll(
			'SELECT part_id FROM part_visits'
		);
		Assert::count(2, $partVisits);
	}

	public function testIteratingOverAllPages() {
		$this->database->query(
			'INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, NOW()), (2, NOW()), (2, NOW() - INTERVAL 5 MINUTE)'
		);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.google.com", "//a", "a")'
		);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.facedown.cz", "//c", "c")'
		);
		$this->database->query(
			'INSERT INTO subscribed_parts (part_id, subscriber_id, `interval`) VALUES
			(1, 1, "PT1M"), (2, 2, "PT2M")'
		);
		$parts = (new Subscribing\CollectiveParts(
			$this->database,
			new Http\FakeBrowser()
		))->iterate();
		Assert::count(2, $parts);
		Assert::same('//a', (string)$parts[0]->print()['expression']);
		Assert::same('//c', (string)$parts[1]->print()['expression']);
	}

	public function testRemovingParts() {
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.facedown.cz", "//b", "b")'
		);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.facedown.cz", "//d", "c")'
		);
		$this->database->query(
			'INSERT INTO subscribed_parts (part_id, subscriber_id, `interval`) VALUES
			(1, 2, "PT2M"), (2, 1, "PT3M")'
		);
		(new Subscribing\CollectiveParts(
			$this->database,
			new Http\FakeBrowser()
		))->remove('www.facedown.cz', '//b');
		$parts = $this->database->fetchAll('SELECT ID FROM parts');
		Assert::count(1, $parts);
		Assert::same(2, $parts[0]['ID']);
	}

    protected function prepareDatabase() {
        $this->database->query('TRUNCATE parts');
        $this->database->query('TRUNCATE part_visits');
		$this->database->query('TRUNCATE pages');
		$this->database->query('TRUNCATE subscribers');
		$this->database->query('TRUNCATE subscribed_parts');
		$this->database->query(
			'INSERT INTO pages (url, content) VALUES
			("www.google.com", "<p>google</p>")'
		);
		$this->database->query(
			'INSERT INTO pages (url, content) VALUES
			("www.facedown.cz", "<p>facedown</p>")'
		);
    }
}

(new CollectiveParts)->run();
