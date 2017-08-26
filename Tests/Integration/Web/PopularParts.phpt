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
		(new Misc\SamplePart($this->database, ['content' => 'foo']))->try();
		(new Misc\SamplePart($this->database, ['content' => 'bar']))->try();
		(new Misc\SamplePart($this->database, ['content' => 'kar']))->try();
		(new Misc\SamplePart($this->database, ['content' => 'baz']))->try();
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
		(new Misc\SamplePart($this->database))->try();
		(new Misc\SamplePart($this->database))->try();
		Assert::same(
			2,
			(new Web\PopularParts(
				new Web\FakeParts(),
				$this->database
			))->count()
		);
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