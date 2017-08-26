<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Web;

use Klapuch\Dataset;
use Remembrall\Misc;
use Remembrall\Model\Web;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class PopularParts extends \Tester\TestCase {
	use TestCase\Database;

	public function testIterating() {
		$this->database->exec(
			"INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES
			(1, 'foo.cz', ROW('//foo', 'xpath'), 'foo', 'fooSnap'),
			(2, 'bar.cz', ROW('//bar', 'xpath'), 'bar', 'barSnap'),
			(3, 'kar.cz', ROW('//kar', 'xpath'), 'kar', 'karSnap'),
			(4, 'baz.cz', ROW('//baz', 'xpath'), 'baz', 'bazSnap')"
		);
		(new Misc\SampleSubscription($this->database, ['part' => 2]))->try();
		(new Misc\SampleSubscription($this->database, ['part' => 2]))->try();
		(new Misc\SampleSubscription($this->database, ['part' => 3]))->try();
		(new Misc\SampleSubscription($this->database, ['part' => 4]))->try();
		(new Misc\SampleSubscription($this->database, ['part' => 2]))->try();
		(new Misc\SampleSubscription($this->database, ['part' => 4]))->try();
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
			(2, 'bar.cz', ROW('//bar', 'xpath'), 'bar', 'barSnap'),
			(4, 'baz.cz', ROW('//baz', 'xpath'), 'baz', 'bazSnap')"
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
}

(new PopularParts)->run();