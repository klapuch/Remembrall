<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Subscribing;

use Klapuch\{
	Output, Dataset
};
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
		))->iterate(new Dataset\FakeSelection(''));
		$part = $parts->current();
		Assert::same('bar', $part->content());
		$parts->next();
		$part = $parts->current();
		Assert::same('baz', $part->content());
		$parts->next();
		$part = $parts->current();
		Assert::same('kar', $part->content());
		$parts->next();
		Assert::null($parts->current());
	}

	public function testEmptyIterating() {
		$parts = (new Subscribing\PopularParts(
			new Subscribing\FakeParts(),
			$this->database
		))->iterate(new Dataset\FakeSelection(''));
		Assert::null($parts->current());
	}

	protected function prepareDatabase() {
		$this->purge(['parts', 'subscriptions']);
	}
}

(new PopularParts)->run();