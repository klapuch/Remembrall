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
		$this->database->query(
			'INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, "2000-01-01 01:01:01")'
		);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.google.com", "//p", "a")'
		);
        (new Subscribing\OwnedParts(
			new Subscribing\FakeParts(),
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
			'SELECT parts.ID, page_url, content, expression, `interval` 
			FROM parts
			INNER JOIN subscribed_parts ON subscribed_parts.part_id = parts.ID'
		);
		Assert::count(1, $parts);
		$part = current($parts);
		Assert::same(1, $part['ID']);
		Assert::same('www.google.com', $part['page_url']);
		Assert::same('a', $part['content']);
		Assert::same('//p', $part['expression']);
		Assert::same('PT158M', $part['interval']);
		$partVisits = $this->database->fetchAll('SELECT part_id, visited_at FROM part_visits');
		Assert::count(1, $partVisits);
		$partVisit = current($partVisits);
		Assert::same(1, $partVisit['part_id']);
		Assert::same('2000-01-01 01:01:01', (string)$partVisit['visited_at']);
    }

	public function testSubscribingDuplicateWithRollback() {
		$this->database->query(
			'INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, "2000-01-01 01:01:01")'
		);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.google.com", "//p", "a")'
		);
		$parts = new Subscribing\OwnedParts(
			new Subscribing\FakeParts(),
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
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.google.com", "//a", "a")'
		);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.facedown.cz", "//b", "b")'
		);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.facedown.cz", "//c", "c")'
		);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.google.com", "//d", "d")'
		);
		$this->database->query(
			'INSERT INTO subscribed_parts (part_id, subscriber_id, `interval`) VALUES
			(1, 1, "PT1M"), (2, 2, "PT2M"), (3, 1, "PT3M"), (4, 1, "PT4M")'
		);
		$parts = (new Subscribing\OwnedParts(
			new Subscribing\FakeParts(),
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
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.google.com", "//a", "a")'
		);
		$this->database->query(
			'INSERT INTO subscribed_parts (part_id, subscriber_id, `interval`) VALUES
			(1, 666, "PT1M")'
		);
		(new Subscribing\OwnedParts(
			new Subscribing\FakeParts(),
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
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.facedown.cz", "//p", "a"), ("www.google.com", "//p", "a")'
		);
		$this->database->query(
			'INSERT INTO subscribed_parts (part_id, subscriber_id, `interval`) VALUES
			(1, 666, "PT1M"), (2, 10, "PT1M")'
		);
		Assert::noError(function() {
			(new Subscribing\OwnedParts(
				new Subscribing\FakeParts(),
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
		});
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
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.google.com", "//b", "b")'
		);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.facedown.cz", "//b", "c")'
		);
		$this->database->query(
			'INSERT INTO subscribed_parts (part_id, subscriber_id, `interval`) VALUES
			(1, 2, "PT2M"), (2, 666, "PT3M")'
		);
		(new Subscribing\OwnedParts(
			new Subscribing\FakeParts(),
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
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.google.com", "//b", "b")'
		);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.facedown.cz", "//b", "c")'
		);
		$this->database->query(
			'INSERT INTO subscribed_parts (part_id, subscriber_id, `interval`) VALUES
			(1, 2, "PT2M"), (2, 666, "PT3M")'
		);
		(new Subscribing\OwnedParts(
			new Subscribing\FakeParts(),
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
		$parts = $this->database->fetchAll('SELECT ID FROM subscribed_parts');
		Assert::count(1, $parts);
		Assert::same(1, $parts[0]['ID']);
	}

    protected function prepareDatabase() {
        $this->database->query('TRUNCATE parts');
        $this->database->query('TRUNCATE part_visits');
		$this->database->query('TRUNCATE pages');
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

(new OwnedParts)->run();
