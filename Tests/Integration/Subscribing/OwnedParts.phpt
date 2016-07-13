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

final class OwnedParts extends TestCase\Database {
    public function testSubscribingBrandNew() {
        (new Subscribing\OwnedParts(
            $this->database,
            new Access\FakeSubscriber(666)
        ))->subscribe(
            new Subscribing\FakePart(
				new Subscribing\FakePage('www.google.com'),
				new Subscribing\FakeExpression('//p'),
				'<p>Content</p>',
                false
            ),
            new Subscribing\FakeInterval(
                new \DateTimeImmutable('2000-01-01 01:01:01'),
                null,
                new \DateInterval('PT158M')
            )
        );
		$parts = $this->database->fetchAll(
			'SELECT ID, page_id, content, expression, `interval`
			FROM parts'
		);
		Assert::count(1, $parts);
		$part = current($parts);
		Assert::same(1, $part['ID']);
		Assert::same(1, $part['page_id']);
		Assert::same('<p>Content</p>', $part['content']);
		Assert::same('//p', $part['expression']);
		Assert::same('PT158M', $part['interval']);
		$partVisits = $this->database->fetchAll('SELECT part_id, visited_at FROM part_visits');
		Assert::count(1, $partVisits);
		$partVisit = current($partVisits);
		Assert::same(1, $partVisit['part_id']);
		Assert::same('2000-01-01 01:01:01', (string)$partVisit['visited_at']);
    }

	public function testSubscribingDuplicateWithRollback() {
		$parts = new Subscribing\OwnedParts(
			$this->database,
			new Access\FakeSubscriber(666)
		);
		$parts->subscribe(
			new Subscribing\FakePart(
				new Subscribing\FakePage('www.google.com'),
				new Subscribing\FakeExpression('//p'),
				'<p>Content</p>',
				false
			),
			new Subscribing\FakeInterval(
				new \DateTimeImmutable('2000-01-01 01:01:01'),
				null,
				new \DateInterval('PT2M')
			)
		);
		Assert::exception(function() use($parts) {
			$parts->subscribe(
				new Subscribing\FakePart(
					new Subscribing\FakePage('www.google.com'),
					new Subscribing\FakeExpression('//p'),
					'<p>Content</p>',
					false
				),
				new Subscribing\FakeInterval(
					new \DateTimeImmutable('2000-01-01 01:01:01'),
					null,
					new \DateInterval('PT2M')
				)
			);
		}, 'Remembrall\Exception\DuplicateException');
		Assert::count(1, $this->database->fetchAll('SELECT ID FROM parts'));
		Assert::count(1, $this->database->fetchAll('SELECT ID FROM part_visits'));
	}

	public function testIteratingOwnedParts() {
		$this->database->query(
			'INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, NOW()), (2, NOW()), (3, NOW()), (4, NOW())'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content, `interval`, subscriber_id) VALUES
			(1, "//a", "a", "PT1M", 1)'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content, `interval`, subscriber_id) VALUES
			(2, "//b", "b", "PT2M", 2)'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content, `interval`, subscriber_id) VALUES
			(2, "//c", "c", "PT3M", 1)'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content, `interval`, subscriber_id) VALUES
			(1, "//d", "d", "PT4M", 1)'
		);
		$parts = (new Subscribing\OwnedParts(
			$this->database,
			new Access\FakeSubscriber(1)
		))->iterate();
		Assert::count(3, $parts);
		Assert::same('//a', (string)$parts[0]->expression());
		Assert::same('//c', (string)$parts[1]->expression());
		Assert::same('//d', (string)$parts[2]->expression());
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException You do not own this part
	 */
	public function testReplacingForeign() {
		$this->database->query(
			'INSERT INTO part_visits (part_id, visited_at) VALUES (1, NOW())'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content, `interval`, subscriber_id) VALUES
			(1, "//a", "a", "PT1M", 666)'
		);
		(new Subscribing\OwnedParts(
			$this->database,
			new Access\FakeSubscriber(666)
		))->replace(
			new Subscribing\FakePart(
				new Subscribing\FakePage('www.google.com'),
				new Subscribing\FakeExpression('//a'),
				'xxx',
				$equals = false
			),
			new Subscribing\FakePart()
		);
	}

	public function testReplacingOwnedPartWithoutError() {
		$this->database->query(
			'INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, NOW()), (2, NOW()), (3, NOW()), (4, NOW())'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content, `interval`, subscriber_id) VALUES
			(1, "//p", "a", "PT1M", 666), (1, "//p", "a", "PT1M", 10)'
		);
		(new Subscribing\OwnedParts(
			$this->database,
			new Access\FakeSubscriber(666)
		))->replace(
			new Subscribing\FakePart(
				new Subscribing\FakePage('www.google.com'),
				new Subscribing\FakeExpression('//p'),
				'a',
				$equals = true
			),
			new Subscribing\FakePart(
				null,
				null,
				'newContent'
			)
		);
		$parts = $this->database->fetchAll('SELECT * FROM parts');
		Assert::count(2, $parts);
		Assert::same('newContent', $parts[0]['content']); // changed
		Assert::same('//p', $parts[0]['expression']);
		Assert::same(1, $parts[0]['page_id']);
		Assert::same(666, $parts[0]['subscriber_id']);

		Assert::same('a', $parts[1]['content']);
		Assert::same('//p', $parts[1]['expression']);
		Assert::same(1, $parts[1]['page_id']);
		Assert::same(10, $parts[1]['subscriber_id']);
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException You do not own this part
	 */
	public function testRemovingForeign() {
		$this->database->query(
			'INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, NOW()), (2, NOW())'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content, `interval`, subscriber_id) VALUES
			(1, "//b", "b", "PT2M", 2)'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content, `interval`, subscriber_id) VALUES
			(2, "//b", "c", "PT3M", 666)'
		);
		(new Subscribing\OwnedParts(
			$this->database,
			new Access\FakeSubscriber(666)
		))->remove(
			new Subscribing\FakePart(
				new Subscribing\FakePage('www.facedown.cz'),
				new Subscribing\FakeExpression('//b'),
				'xxx',
				$equals = false
			)
		);
	}

	public function testRemovingOwned() {
		$this->database->query(
			'INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, NOW()), (2, NOW())'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content, `interval`, subscriber_id) VALUES
			(1, "//b", "b", "PT2M", 2)'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content, `interval`, subscriber_id) VALUES
			(2, "//b", "c", "PT3M", 666)'
		);
		(new Subscribing\OwnedParts(
			$this->database,
			new Access\FakeSubscriber(666)
		))->remove(
			new Subscribing\FakePart(
				new Subscribing\FakePage('www.facedown.cz'),
				new Subscribing\FakeExpression('//b'),
				'c',
				$equals = true
			)
		);
		$parts = $this->database->fetchAll('SELECT ID FROM parts');
		Assert::count(1, $parts);
		Assert::same(1, $parts[0]['ID']);
	}

    protected function prepareDatabase() {
        $this->database->query('TRUNCATE parts');
        $this->database->query('TRUNCATE part_visits');
		$this->database->query('TRUNCATE pages');
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

(new OwnedParts)->run();
