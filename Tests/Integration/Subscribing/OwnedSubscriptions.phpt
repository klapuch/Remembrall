<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use Remembrall\Model\{
	Subscribing, Access
};
use Remembrall\TestCase;
use Tester\Assert;
use Klapuch\{
	Output, Uri, Time
};

require __DIR__ . '/../../bootstrap.php';

final class OwnedSubscriptions extends TestCase\Database {
    public function testSubscribingBrandNew() {
		$this->database->query(
			"INSERT INTO parts (page_url, expression, content) VALUES
			('www.google.com', '//p', 'a')"
		);
		$this->purge(['part_visits']);
		$this->database->query(
			"INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, '2000-01-01 01:01:01')"
		);
		(new Subscribing\OwnedSubscriptions(
			new Access\FakeSubscriber(666),
            $this->database
        ))->subscribe(
			new Uri\FakeUri('www.google.com'),
			'//p',
            new Time\FakeInterval(
                new \DateTimeImmutable('01:01'),
                null,
				'PT120S'
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
		Assert::same('PT120S', $part['interval']);
		$partVisits = $this->database->fetchAll('SELECT part_id, visited_at FROM part_visits');
		Assert::count(1, $partVisits);
		$partVisit = current($partVisits);
		Assert::same(1, $partVisit['part_id']);
		Assert::same('2000-01-01 01:01:01', (string)$partVisit['visited_at']);
    }

	public function testSubscribingDuplicateWithRollback() {
		$this->database->query(
			"INSERT INTO parts (page_url, expression, content) VALUES
			('www.google.com', '//p', 'a')"
		);
		$this->purge(['part_visits']);
		$this->database->query(
			"INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, '2000-01-01 01:01:01')"
		);
		$parts = new Subscribing\OwnedSubscriptions(
			new Access\FakeSubscriber(666),
			$this->database
		);
		$parts->subscribe(
			new Uri\FakeUri('www.google.com'),
			'//p',
			new Time\FakeInterval(
				new \DateTimeImmutable('01:01'),
				null,
				'PT120S'
			)
		);
		Assert::exception(function() use($parts) {
			$parts->subscribe(
				new Uri\FakeUri('www.google.com'),
				'//p',
				new Time\FakeInterval(
					new \DateTimeImmutable('01:01'),
					null,
					'PT120S'
				)
			);
		}, 'Remembrall\Exception\DuplicateException');
		Assert::count(1, $this->database->fetchAll('SELECT id FROM parts'));
		Assert::count(1, $this->database->fetchAll('SELECT id FROM part_visits'));
	}

	public function testPrintingOwnedSubscriptions() {
		$this->database->query(
			"INSERT INTO parts (page_url, expression, content) VALUES
			('https://www.google.com', '//a', 'a'),
			('http://www.facedown.cz', '//b', 'b'),
			('http://www.facedown.cz', '//c', 'c'),
			('https://www.google.com', '//d', 'd')"
		);
		$this->database->query(
			"INSERT INTO subscriptions (part_id, subscriber_id, interval, last_update) VALUES
			(1, 1, 'PT1M', '1993-01-01'),
			(2, 2, 'PT2M', '1994-01-01'),
			(3, 1, 'PT3M', '1996-01-01'),
			(4, 1, 'PT4M', '1997-01-01')"
		);
		$this->purge(['part_visits']);
		$this->database->query(
			"INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, '2000-01-01 01:01:01'),
			(1, '2008-01-01 01:01:01'),
			(2, '2001-01-01 01:01:01'),
			(3, '2002-01-01 01:01:01'),
			(4, '2003-01-01 01:01:01')"
		);
		$subscriptions = (new Subscribing\OwnedSubscriptions(
			new Access\FakeSubscriber(1, 'idk@email.cz'),
			$this->database
		))->print(new Output\FakeFormat(''));
		Assert::count(3, $subscriptions);
        Assert::contains('1993-01-01', (string)$subscriptions[0]);
        Assert::contains('1997-01-01', (string)$subscriptions[1]);
        Assert::contains('1996-01-01', (string)$subscriptions[2]);
    }

	public function testEmptySubscriptions() {
		Assert::same(
			[],
			(new Subscribing\OwnedSubscriptions(
				new Access\FakeSubscriber(1),
				$this->database
			))->print(new Output\FakeFormat(''))
		);
	}

    protected function prepareDatabase() {
		$this->truncate(['parts', 'part_visits', 'pages', 'subscriptions']);
		$this->restartSequence(['parts', 'part_visits', 'subscriptions']);
		$this->database->query(
			"INSERT INTO pages (url, content) VALUES
			('www.google.com', '<p>google</p>'),
			('www.facedown.cz', '<p>facedown</p>')"
		);
    }
}

(new OwnedSubscriptions)->run();
