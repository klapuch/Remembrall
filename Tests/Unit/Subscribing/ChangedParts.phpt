<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Subscribing;

use Remembrall\Model\{
	Subscribing, Access
};
use Remembrall\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ChangedParts extends Tester\TestCase {
	public function testIterating() {
		$allParts = [
			new Subscribing\FakePart(
				new Subscribing\FakePage('google.com'),
				new Subscribing\FakeExpression('//p'),
				'a',
				false
			),
			new Subscribing\FakePart(
				new Subscribing\FakePage('google.com'),
				new Subscribing\FakeExpression('//p'),
				'b',
				true
			),
			new Subscribing\FakePart(
				new Subscribing\FakePage('google.com'),
				new Subscribing\FakeExpression('//p'),
				'c',
				false
			),
		];
		Assert::equal(
			[
				0 => new Subscribing\FakePart(
					new Subscribing\FakePage('google.com'),
					new Subscribing\FakeExpression('//p'),
					'a',
					false
				),
				2 => new Subscribing\FakePart(
					new Subscribing\FakePage('google.com'),
					new Subscribing\FakeExpression('//p'),
					'c',
					false
				),
			],
			(new Subscribing\ChangedParts(
				new Subscribing\FakeParts($allParts)
			))->iterate()
		);
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException This part has not changed yet
	 */
	public function testReplacingUnchangedPart() {
		$allParts = [
			new Subscribing\FakePart(
				new Subscribing\FakePage('google.com'),
				new Subscribing\FakeExpression('//p'),
				'a',
				false
			),
			new Subscribing\FakePart(
				new Subscribing\FakePage('google.com'),
				new Subscribing\FakeExpression('//p'),
				'c',
				false
			),
		];
		(new Subscribing\ChangedParts(
			new Subscribing\FakeParts($allParts)
		))->replace(
			new Subscribing\FakePart(
				new Subscribing\FakePage('google.com'),
				new Subscribing\FakeExpression('//p'),
				'c',
				false // unchanged
			),
			new Subscribing\FakePart()
		);
	}

	public function testReplacingChangedPartWithNoError() {
		$allParts = [
			new Subscribing\FakePart(
				new Subscribing\FakePage('google.com'),
				new Subscribing\FakeExpression('//p'),
				'a',
				false
			),
			new Subscribing\FakePart(
				new Subscribing\FakePage('google.com'),
				new Subscribing\FakeExpression('//p'),
				'c',
				false
			),
		];
		(new Subscribing\ChangedParts(
			new Subscribing\FakeParts($allParts)
		))->replace(
			new Subscribing\FakePart(
				new Subscribing\FakePage('google.com'),
				new Subscribing\FakeExpression('//p'),
				'c',
				true // changed
			),
			new Subscribing\FakePart()
		);
		Assert::true(true);
	}
}

(new ChangedParts())->run();
