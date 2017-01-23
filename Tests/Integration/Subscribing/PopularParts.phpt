<?php
declare(strict_types = 1);
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

final class PopularParts extends TestCase\Database {
	public function testIterating() {
		$this->database->exec(
			"INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES
			(1, 'foo.cz', '//foo', 'foo', 'fooSnap'),
			(2, 'bar.cz', '//bar', 'bar', 'barSnap'),
			(3, 'kar.cz', '//kar', 'kar', 'karSnap'),
			(4, 'baz.cz', '//baz', 'baz', 'bazSnap')"
		);
		$this->database->exec(
			"INSERT INTO subscriptions (user_id, part_id, interval, last_update, snapshot) VALUES
			(1, 2, 'PT6M', NOW(), md5(random()::text)),
			(2, 2, 'PT6M', NOW(), md5(random()::text)),
			(3, 3, 'PT6M', NOW(), md5(random()::text)),
			(4, 4, 'PT6M', NOW(), md5(random()::text)),
			(5, 2, 'PT6M', NOW(), md5(random()::text)),
			(6, 4, 'PT6M', NOW(), md5(random()::text))"
		);
		$parts = (new Subscribing\PopularParts(
			new Subscribing\FakeParts(),
			$this->database
		))->getIterator();
		$part = $parts->current();
		Assert::same('barSnap', $part->snapshot());
		$parts->next();
		$part = $parts->current();
		Assert::same('bazSnap', $part->snapshot());
		$parts->next();
		$part = $parts->current();
		Assert::same('karSnap', $part->snapshot());
		$parts->next();
		Assert::null($parts->current());
	}

	public function testEmptyIterating() {
		$parts = (new Subscribing\PopularParts(
			new Subscribing\FakeParts(),
			$this->database
		))->getIterator();
		Assert::null($parts->current());
	}

	public function testPrinting() {
		$this->database->exec(
			"INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES
			(1, 'foo.cz', '//foo', 'foo', 'fooSnap'),
			(2, 'bar.cz', '//bar', 'bar', 'barSnap'),
			(3, 'kar.cz', '//kar', 'kar', 'karSnap'),
			(4, 'baz.cz', '//baz', 'baz', 'bazSnap')"
		);
		$this->database->exec(
			"INSERT INTO subscriptions (user_id, part_id, interval, last_update, snapshot) VALUES
			(1, 2, 'PT6M', NOW(), md5(random()::text)),
			(2, 2, 'PT6M', NOW(), md5(random()::text)),
			(3, 3, 'PT6M', NOW(), md5(random()::text)),
			(4, 4, 'PT6M', NOW(), md5(random()::text)),
			(5, 2, 'PT6M', NOW(), md5(random()::text)),
			(6, 4, 'PT6M', NOW(), md5(random()::text))"
		);
		$parts = (new Subscribing\PopularParts(
			new Subscribing\FakeParts(),
			$this->database
		))->print(new Output\FakeFormat(''));
		Assert::count(3, $parts);
		Assert::contains('bar.cz', $parts[0]->serialization());
		Assert::contains('baz.cz', $parts[1]->serialization());
		Assert::contains('kar.cz', $parts[2]->serialization());
	}

	public function testEmptyPrinting() {
		$parts = (new Subscribing\PopularParts(
			new Subscribing\FakeParts(),
			$this->database
		))->print(new Output\FakeFormat(''));
		Assert::count(0, $parts);
	}

	protected function prepareDatabase() {
		$this->purge(['parts', 'subscriptions']);
	}
}

(new PopularParts)->run();