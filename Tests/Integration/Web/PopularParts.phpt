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

final class PopularParts extends \Tester\TestCase {
	use TestCase\Database;

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
		$parts = (new Web\PopularParts(
			new Web\FakeParts(),
			$this->database
		))->all(new Dataset\FakeSelection(''));
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

	public function testCounting() {
		$this->database->exec(
			"INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES
			(2, 'bar.cz', '//bar', 'bar', 'barSnap'),
			(4, 'baz.cz', '//baz', 'baz', 'bazSnap')"
		);
		$parts = new Web\PopularParts(
			new Web\FakeParts(),
			$this->database
		);
		Assert::same(2, $parts->count());
	}

	public function testEmptyIterating() {
		$parts = (new Web\PopularParts(
			new Web\FakeParts(),
			$this->database
		))->all(new Dataset\FakeSelection(''));
		Assert::null($parts->current());
	}

	protected function prepareDatabase(): void {
		$this->purge(['parts', 'subscriptions']);
	}
}

(new PopularParts)->run();