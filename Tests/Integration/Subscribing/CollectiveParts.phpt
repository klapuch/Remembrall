<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use Dibi;
use Remembrall\Model\{
	Subscribing, Access
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
            $this->database
        ))->subscribe(
            new Subscribing\FakePart(
				new Subscribing\FakePage('www.google.com'),
				new Subscribing\FakeExpression('//p'),
				'<p>Content</p>',
                false
            ),
            new Subscribing\FakeInterval(
                new \DateTimeImmutable('2000-01-01 01:01:01')
            )
        );
		$parts = $this->database->fetchAll(
			'SELECT ID, page_id, content, expression FROM parts'
		);
		Assert::count(1, $parts);
		Assert::same(1, $parts[0]['ID']);
		Assert::same(1, $parts[0]['page_id']);
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
			$this->database
		))->subscribe(
			new Subscribing\FakePart(
				new Subscribing\FakePage('www.google.com'),
				new Subscribing\FakeExpression('//p'),
				'<p>Content</p>',
				false
			),
			new Subscribing\FakeInterval(
				new \DateTimeImmutable('2000-01-01 01:01:01')
			)
		); //once
		(new Subscribing\CollectiveParts(
			$this->database
		))->subscribe(
			new Subscribing\FakePart(
				new Subscribing\FakePage('www.google.com'),
				new Subscribing\FakeExpression('//p'),
				'<p>Updated content</p>',
				false
			),
			new Subscribing\FakeInterval(
				new \DateTimeImmutable('2002-01-01 01:01:01')
			)
		); //twice
		$parts = $this->database->fetchAll(
			'SELECT ID, page_id, content, expression FROM parts'
		);
		Assert::count(1, $parts);
		Assert::same(1, $parts[0]['ID']);
		Assert::same(1, $parts[0]['page_id']);
		Assert::same('<p>Updated content</p>', $parts[0]['content']);
		Assert::same('//p', $parts[0]['expression']);
		$partVisits = $this->database->fetchAll(
			'SELECT part_id FROM part_visits'
		);
		Assert::count(2, $partVisits);
	}

	public function testReplacing() {
		$this->database->query(
			'INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, "2000-01-01 01:01:01")'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content) VALUES
			(1, "//p", "a")'
		);
		$this->database->query(
			'INSERT INTO subscribed_parts (part_id, subscriber_id, `interval`) VALUES
			(1, 666, "PT1M")'
		);
		(new Subscribing\CollectiveParts($this->database))->replace(
			new Subscribing\FakePart(
				new Subscribing\FakePage('www.google.com'),
				new Subscribing\FakeExpression('//p'),
				'c',
				true // owned
			),
			new Subscribing\FakePart(
				null,
				new Subscribing\FakeExpression('//x'),
				'newContent',
				false
			)
		);
		$parts = $this->database->fetchAll(
			'SELECT content, subscriber_id, expression, page_id,
			part_visits.visited_at
			FROM parts
			INNER JOIN part_visits ON part_visits.part_id = parts.ID
			INNER JOIN subscribed_parts ON subscribed_parts.part_id = parts.ID'
		);
		Assert::count(1, $parts);
		$part = current($parts);
		Assert::same('newContent', $part['content']); // changed
		Assert::same(666, $part['subscriber_id']); // without change
		Assert::same('//p', $part['expression']); // without change
		Assert::same(1, $part['page_id']); // without change
		Assert::notSame('2000-01-01 01:01:01', (string)$part['visited_at']);
	}

	public function testIteratingOverAllPages() {
		$this->database->query(
			'INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, NOW()), (2, NOW()), (3, NOW()), (4, NOW())'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content) VALUES
			(1, "//a", "a")'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content) VALUES
			(2, "//b", "b")'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content) VALUES
			(2, "//c", "c")'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content) VALUES
			(1, "//d", "d")'
		);
		$this->database->query(
			'INSERT INTO subscribed_parts (part_id, subscriber_id, `interval`) VALUES
			(1, 1, "PT1M"), (2, 2, "PT2M"), (3, 1, "PT3M"), (4, 1, "PT4M")'
		);
		$parts = (new Subscribing\CollectiveParts(
			$this->database
		))->iterate();
		Assert::count(4, $parts);
		Assert::same('//a', (string)$parts[0]->expression());
		Assert::same('//b', (string)$parts[1]->expression());
		Assert::same('//c', (string)$parts[2]->expression());
		Assert::same('//d', (string)$parts[3]->expression());
	}

	public function testRemovingAllSameParts() {
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content) VALUES
			(2, "//b", "b")'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content) VALUES
			(2, "//d", "c")'
		);
		$this->database->query(
			'INSERT INTO subscribed_parts (part_id, subscriber_id, `interval`) VALUES
			(1, 2, "PT2M"), (2, 1, "PT3M")'
		);
		(new Subscribing\CollectiveParts(
			$this->database
		))->remove(
			new Subscribing\FakePart(
				new Subscribing\FakePage('www.facedown.cz'),
				new Subscribing\FakeExpression('//b')
			)
		);
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
			'INSERT INTO pages (ID, url, content) VALUES
			(1, "www.google.com", "<p>google</p>")'
		);
		$this->database->query(
			'INSERT INTO pages (ID, url, content) VALUES
			(2, "www.facedown.cz", "<p>facedown</p>")'
		);
    }
}

(new CollectiveParts)->run();
