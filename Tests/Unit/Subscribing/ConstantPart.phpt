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

final class ConstantPart extends Tester\TestCase {
	public function testSameContentButDifferentPage() {
		Assert::false(
			(new Subscribing\ConstantPart(
				new Subscribing\FakePage('google.com'),
				'',
				new Subscribing\FakeExpression,
				new Subscribing\FakeSubscriber()
			))->equals(
				new Subscribing\FakePart(
					'',
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
				new Subscribing\FakeSubscriber()
			))->equals(
				new Subscribing\FakePart(
					'',
					new Subscribing\FakePage('google.com')
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
				new Subscribing\FakeSubscriber()
			))->equals(
				new Subscribing\FakePart(
					'abc',
					new Subscribing\FakePage('google.com')
				)
			)
		);
	}
}

(new ConstantPart())->run();
