<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Web;

use Klapuch\Dataset;
use Remembrall\Model\Web;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class UnreliableParts extends \Tester\TestCase {
	use TestCase\Database;

	public function testIterating() {
		$this->database->exec(
			"INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES
			(1, 'www.google.com', '//a', 'a', ''),
			(2, 'www.facedown.cz', '//b', 'b', ''),
			(3, 'www.google.com', '//c', 'c', ''),
			(4, 'www.facedown.cz', '//d', 'd', ''),
			(5, 'www.new.cz', '//e', 'e', '')"
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
		$parts = (new Web\UnreliableParts(
			new Web\FakeParts(),
			$this->database
		))->all(new Dataset\FakeSelection(''));
		$part = $parts->current();
		Assert::equal('d', $part->content());
		$parts->next();
		$part = $parts->current();
		Assert::equal('a', $part->content());
		$parts->next();
		Assert::null($parts->current());
	}

	public function testCounting() {
		$this->database->exec(
			"INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES
			(1, 'www.google.com', '//a', 'a', ''),
			(2, 'www.facedown.cz', '//d', 'd', '')"
		);
		$this->truncate(['part_visits']);
		$this->database->exec(
			"INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, NOW() - INTERVAL '50 SECOND'),
			(2, NOW() - INTERVAL '45 SECOND')"
		);
		$this->database->exec(
			"INSERT INTO subscriptions (part_id, user_id, interval, last_update, snapshot) VALUES
			(1, 1, 'PT10S', NOW(), ''),
			(2, 1, 'PT10S', NOW(), '')"
		);
		$parts = new Web\UnreliableParts(
			new Web\FakeParts(),
			$this->database
		);
		$count = 2;
		Assert::same($count, $parts->count());
		Assert::same(
			$count,
			iterator_count($parts->all(new Dataset\FakeSelection('')))
		);
	}

	public function testEmptyIterating() {
		$parts = (new Web\UnreliableParts(
			new Web\FakeParts(),
			$this->database
		))->all(new Dataset\FakeSelection(''));
		Assert::null($parts->current());
	}
}

(new UnreliableParts)->run();