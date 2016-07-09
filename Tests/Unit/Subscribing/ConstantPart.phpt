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

final class ConstantPart extends Tester\TestCase {
	public function testDifferentParts() {
		Assert::false(
			(new Subscribing\ConstantPart(
				new Subscribing\FakePage('google.com'),
				'abc',
				new Subscribing\FakeExpression,
				new Subscribing\FakeInterval()
			))->equals(
				new Subscribing\FakePart(
					new Subscribing\FakePage(),
					null,
					null,
					$equals = false
				)
			)
		);
	}

	public function testEquivalentParts() {
		Assert::true(
			(new Subscribing\ConstantPart(
				new Subscribing\FakePage('google.com'),
				'abc',
				new Subscribing\FakeExpression,
				new Subscribing\FakeInterval()
			))->equals(
				new Subscribing\FakePart(
					new Subscribing\FakePage(),
					null,
					null,
					$equals = true
				)
			)
		);
	}
}

(new ConstantPart())->run();
