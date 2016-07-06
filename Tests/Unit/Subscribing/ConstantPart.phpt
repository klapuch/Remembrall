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
	public function testSameContentButDifferentPage() {
		Assert::false(
			(new Subscribing\ConstantPart(
				new Subscribing\FakePage('google.com'),
				'',
				new Subscribing\FakeExpression,
				new Subscribing\FakeInterval()
			))->equals(
				new Subscribing\FakePart(
					new Subscribing\FakePage('seznam.cz')
				)
			)
		);
	}

	public function testDifferentContentButSamePage() {
		Assert::false(
			(new Subscribing\ConstantPart(
				new Subscribing\FakePage('google.com'),
				'abc',
				new Subscribing\FakeExpression,
				new Subscribing\FakeInterval()
			))->equals(
				new Subscribing\FakePart(
					new Subscribing\FakePage('google.com'),
					null,
					''
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
					new Subscribing\FakePage('google.com'),
					null,
					'abc'
				)
			)
		);
	}
}

(new ConstantPart())->run();
