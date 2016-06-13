<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Subscribing;

use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ChangedParts extends Tester\TestCase {
	public function testIterating() {
		$allParts = [
			new Subscribing\FakePart(
				'a',
				new Subscribing\FakePage('google.com'),
				false,
				new Subscribing\FakeExpression('//p')
			),
			new Subscribing\FakePart(
				'b',
				new Subscribing\FakePage('google.com'),
				true,
				new Subscribing\FakeExpression('//p')
			),
			new Subscribing\FakePart(
				'c',
				new Subscribing\FakePage('google.com'),
				false,
				new Subscribing\FakeExpression('//p')
			),
		];
		Assert::equal(
			[
				0 => new Subscribing\FakePart(
					'a',
					new Subscribing\FakePage('google.com'),
					false,
					new Subscribing\FakeExpression('//p')
				),
				2 => new Subscribing\FakePart(
					'c',
					new Subscribing\FakePage('google.com'),
					false,
					new Subscribing\FakeExpression('//p')
				),
			],
			(
			new Subscribing\ChangedParts(
				new Subscribing\FakeParts($allParts)
			)
			)->iterate()
		);
	}
}

(new ChangedParts())->run();
