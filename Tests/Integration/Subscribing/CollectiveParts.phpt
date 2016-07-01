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
                '<p>Content</p>',
                new Subscribing\FakePage('www.google.com'),
                false,
                new Subscribing\FakeExpression('//p')
            ),
            new Subscribing\FakeInterval(
                new \DateTimeImmutable('2000-01-01 01:01:01'),
                null,
                new \DateInterval('PT15M')
            )
        );
		$parts = $this->database->fetchAll(
			'SELECT ID, page_id, content, expression, `interval`
			FROM parts'
		);
		Assert::count(2, $parts);
		Assert::same(1, $parts[0]['ID']);
		Assert::same(1, $parts[0]['page_id']);
		Assert::same('<p>Content</p>', $parts[0]['content']);
		Assert::same('//p', $parts[0]['expression']);
		Assert::same(15, $parts[0]['interval']);
		Assert::same(2, $parts[1]['ID']);
		Assert::same(1, $parts[1]['page_id']);
		Assert::same('<p>Content</p>', $parts[1]['content']);
		Assert::same('//p', $parts[1]['expression']);
		Assert::same(15, $parts[1]['interval']);
		$partVisits = $this->database->fetchAll(
			'SELECT part_id FROM part_visits'
		);
		Assert::count(2, $partVisits);
		//Assert::same(1, $partVisits[0]['part_id']);
    }

	public function testReplacing() {
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content, `interval`, subscriber_id) VALUES
			(1, "//p", "a", 1, 666)'
		);
		(new Subscribing\CollectiveParts(
			$this->database
		))->replace(
			new Subscribing\FakePart(
				'c',
				new Subscribing\FakePage('www.google.com'),
				true, // owned
				new Subscribing\FakeExpression('//p'),
				new Access\FakeSubscriber(666)
			),
			new Subscribing\FakePart(
				'newContent',
				null,
				false,
				new Subscribing\FakeExpression('//x'),
				new Access\FakeSubscriber(888)
			)
		);
		$parts = $this->database->fetchAll(
			'SELECT content, subscriber_id, expression, page_id FROM parts'
		);
		Assert::count(1, $parts);
		$part = current($parts);
		Assert::same('newContent', $part['content']); // changed
		Assert::same(666, $part['subscriber_id']);  // without change
		Assert::same('//p', $part['expression']); // without change
		Assert::same(1, $part['page_id']); // without change
	}

	public function testIteratingOverAllPages() {
		$this->database->query(
			'INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, NOW()), (2, NOW()), (3, NOW()), (4, NOW())'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content, `interval`, subscriber_id) VALUES
			(1, "//a", "a", 1, 1)'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content, `interval`, subscriber_id) VALUES
			(2, "//b", "b", 2, 2)'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content, `interval`, subscriber_id) VALUES
			(2, "//c", "c", 3, 1)'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content, `interval`, subscriber_id) VALUES
			(1, "//d", "d", 4, 1)'
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
			'INSERT INTO parts (page_id, expression, content, `interval`, subscriber_id) VALUES
			(2, "//b", "b", 2, 2)'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content, `interval`, subscriber_id) VALUES
			(2, "//b", "c", 3, 1)'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content, `interval`, subscriber_id) VALUES
			(2, "//d", "c", 3, 1)'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content, `interval`, subscriber_id) VALUES
			(1, "//d", "d", 4, 1)'
		);
		(new Subscribing\CollectiveParts(
			$this->database
		))->remove(
			new Subscribing\FakePart(
				null,
				new Subscribing\FakePage('www.facedown.cz'),
				false,
				new Subscribing\FakeExpression('//b')
			)
		);
		$parts = $this->database->fetchAll('SELECT ID FROM parts');
		Assert::count(2, $parts);
		Assert::same(3, $parts[0]['ID']);
		Assert::same(4, $parts[1]['ID']);
	}

    protected function prepareDatabase() {
        $this->database->query('TRUNCATE parts');
        $this->database->query('TRUNCATE part_visits');
		$this->database->query('TRUNCATE pages');
		$this->database->query('TRUNCATE subscribers');
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
