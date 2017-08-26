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

final class UnreliableParts extends \Tester\TestCase {
	use TestCase\Database;

	public function testIterating() {
		(new Misc\SamplePart($this->database, ['content' => 'a']))->try();
		(new Misc\SamplePart($this->database))->try();
		(new Misc\SamplePart($this->database))->try();
		(new Misc\SamplePart($this->database, ['content' => 'd']))->try();
		(new Misc\SamplePart($this->database))->try();
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
		(new Misc\SampleSubscription($this->database, ['part' => 1, 'interval' => 'PT10S']))->try();
		(new Misc\SampleSubscription($this->database, ['part' => 1, 'interval' => 'PT50S']))->try();
		(new Misc\SampleSubscription($this->database, ['part' => 1, 'interval' => 'PT5S']))->try();
		(new Misc\SampleSubscription($this->database, ['part' => 2, 'interval' => 'PT10S']))->try();
		(new Misc\SampleSubscription($this->database, ['part' => 2, 'interval' => 'PT50S']))->try();
		(new Misc\SampleSubscription($this->database, ['part' => 4, 'interval' => 'PT10S']))->try();
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
		(new Misc\SamplePart($this->database))->try();
		(new Misc\SamplePart($this->database))->try();
		$this->truncate(['part_visits']);
		$this->database->exec(
			"INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, NOW() - INTERVAL '50 SECOND'),
			(2, NOW() - INTERVAL '45 SECOND')"
		);
		(new Misc\SampleSubscription($this->database, ['part' => 1, 'interval' => 'PT10S']))->try();
		(new Misc\SampleSubscription($this->database, ['part' => 2, 'interval' => 'PT10S']))->try();
		$parts = new Web\UnreliableParts(new Web\FakeParts(), $this->database);
		Assert::same(2, $parts->count());
		Assert::same(2, iterator_count($parts->all(new Dataset\FakeSelection(''))));
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