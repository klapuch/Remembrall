<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use Remembrall\Model\{
	Subscribing, Access, Http
};
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class OwnedSubscriptions extends TestCase\Database {
    public function testSubscribingBrandNew() {
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content, content_hash) VALUES
			("www.google.com", "//p", "a", MD5("a"))'
		);
		$this->purge(['part_visits']);
		$this->database->query(
			'INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, "2000-01-01 01:01:01")'
		);
		(new Subscribing\OwnedSubscriptions(
			new Access\FakeSubscriber(666),
            $this->database
        ))->subscribe(
			'www.google.com',
			'//p',
            new Subscribing\FakeInterval(
                new \DateTimeImmutable('01:01'),
                null,
                new \DateInterval('PT158M')
            )
        );
		$parts = $this->database->fetchAll(
			'SELECT subscriptions.part_id AS id, page_url, expression, interval 
			FROM parts
			INNER JOIN subscriptions ON subscriptions.part_id = parts.id'
		);
		Assert::count(1, $parts);
		$part = current($parts);
		Assert::same(1, $part['id']);
		Assert::same('www.google.com', $part['page_url']);
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
			'INSERT INTO parts (page_url, expression, content, content_hash) VALUES
			("www.google.com", "//p", "a", MD5("a"))'
		);
		$this->purge(['part_visits']);
		$this->database->query(
			'INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, "2000-01-01 01:01:01")'
		);
		$parts = new Subscribing\OwnedSubscriptions(
			new Access\FakeSubscriber(666),
			$this->database
		);
		$parts->subscribe(
			'www.google.com',
			'//p',
			new Subscribing\FakeInterval(
				new \DateTimeImmutable('01:01'),
				null,
				new \DateInterval('PT158M')
			)
		);
		Assert::exception(function() use($parts) {
			$parts->subscribe(
				'www.google.com',
				'//p',
				new Subscribing\FakeInterval(
					new \DateTimeImmutable('01:01'),
					null,
					new \DateInterval('PT158M')
				)
			);
		}, 'Remembrall\Exception\DuplicateException');
		Assert::count(1, $this->database->fetchAll('SELECT id FROM parts'));
		Assert::count(1, $this->database->fetchAll('SELECT id FROM part_visits'));
	}

	public function testIteratingOwnedSubscriptions() {
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content, content_hash) VALUES
			("www.google.com", "//a", "a", MD5("a")),
			("www.facedown.cz", "//b", "b", MD5("b")),
			("www.facedown.cz", "//c", "c", MD5("c")),
			("www.google.com", "//d", "d", MD5("d"))'
		);
		$this->database->query(
			'INSERT INTO subscriptions (part_id, subscriber_id, interval, hash) VALUES
			(1, 1, "PT1M", "sample"),
			(2, 2, "PT2M", "sample"),
			(3, 1, "PT3M", "sample"),
			(4, 1, "PT4M", "sample")'
		);
		$this->purge(['part_visits']);
		$this->database->query(
			'INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, "2000-01-01 01:01:01"),
			(1, "2008-01-01 01:01:01"),
			(2, "2001-01-01 01:01:01"),
			(3, "2002-01-01 01:01:01"),
			(4, "2003-01-01 01:01:01")'
		);
		$parts = (new Subscribing\OwnedSubscriptions(
			new Access\FakeSubscriber(1),
			$this->database
		))->iterate();
		Assert::count(3, $parts);
		Assert::same('//a', (string)$parts[0]->print()['expression']);
		Assert::same('2008', $parts[0]->print()['interval']->start()->format('Y'));
		Assert::same('//d', (string)$parts[1]->print()['expression']);
		Assert::same('//c', (string)$parts[2]->print()['expression']);
	}

	public function testEmptySubscriptions() {
		Assert::same(
			[],
			(new Subscribing\OwnedSubscriptions(
				new Access\FakeSubscriber(1),
				$this->database
			))->iterate()
		);
	}

    protected function prepareDatabase() {
		$this->truncate(['parts', 'part_visits', 'pages', 'subscriptions']);
		$this->restartSequence(['parts', 'part_visits', 'subscriptions']);
		$this->database->query(
			'INSERT INTO pages (url, content) VALUES
			("www.google.com", "<p>google</p>"),
			("www.facedown.cz", "<p>facedown</p>")'
		);
    }
}

(new OwnedSubscriptions)->run();
