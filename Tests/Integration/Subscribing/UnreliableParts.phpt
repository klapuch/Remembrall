<?php
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Subscribing;

use Klapuch\Output;
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class UnreliableParts extends TestCase\Database {
	public function testIterating() {
		$parts = (new Subscribing\UnreliableParts(
			new Subscribing\FakeParts(),
			$this->database
		))->getIterator();
		$part = $parts->current();
		Assert::equal('d', $part->content());
		$parts->next();
		$part = $parts->current();
		Assert::equal('a', $part->content());
		$parts->next();
		Assert::null($parts->current());
	}

	public function testEmptyIterating() {
		$this->truncate(['parts', 'part_visits', 'subscriptions']);
		$parts = (new Subscribing\UnreliableParts(
			new Subscribing\FakeParts(),
			$this->database
		))->getIterator();
		Assert::null($parts->current());
	}

	public function testPrinting() {
		$parts = (new Subscribing\UnreliableParts(
			new Subscribing\FakeParts(),
			$this->database
		))->print(new Output\FakeFormat(''));
		Assert::count(2, $parts);
		Assert::contains('//d', $parts[0]->serialization());
		Assert::contains('//a', $parts[1]->serialization());
	}

	public function testEmptyPrinting() {
		$this->truncate(['parts', 'part_visits', 'subscriptions']);
		$parts = (new Subscribing\UnreliableParts(
			new Subscribing\FakeParts(),
			$this->database
		))->print(new Output\FakeFormat(''));
		Assert::count(0, $parts);
	}

	protected function prepareDatabase() {
		$this->truncate(['parts', 'part_visits', 'subscriptions']);
		$this->restartSequence(['parts', 'subscriptions']);
		$this->database->exec(
			"INSERT INTO parts (page_url, expression, content, snapshot) VALUES
			('www.google.com', '//a', 'a', ''),
			('www.facedown.cz', '//b', 'b', ''),
			('www.google.com', '//c', 'c', ''),
			('www.facedown.cz', '//d', 'd', ''),
			('www.new.cz', '//e', 'e', '')"
		);
		$this->truncate(['part_visits']);
		$this->database->exec(
			"INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, NOW() - INTERVAL '50 SECOND'),
			(1, NOW() - INTERVAL '10 SECOND'),
			(1, NOW() - INTERVAL '20 SECOND'),
			(2, NOW()),
			(2, NOW() - INTERVAL '5 SECOND'),
			(4, NOW() - INTERVAL '45 SECOND')"
		);
		$this->database->exec(
			"INSERT INTO subscriptions (part_id, user_id, interval, last_update, snapshot) VALUES
			(1, 1, 'PT10S', NOW(), ''),
			(1, 2, 'PT50S', NOW(), ''),
			(1, 3, 'PT5S', NOW(), ''),
			(2, 3, 'PT10S', NOW(), ''),
			(2, 4, 'PT50S', NOW(), ''),
			(4, 1, 'PT10S', NOW(), '')"
		);
	}
}

(new UnreliableParts)->run();