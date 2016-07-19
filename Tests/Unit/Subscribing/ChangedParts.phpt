<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Subscribing;

use Remembrall\Model\{
	Subscribing, Http
};
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ChangedParts extends Tester\TestCase {
	public function testSubscribingChangedPart() {
		Assert::noError(
			function() {
				(new Subscribing\ChangedParts(
					new Subscribing\FakeParts()
				))->subscribe(
					new Subscribing\FakePart(null, $same = false),
					'www.google.com',
					'//h1',
					new Subscribing\FakeInterval()
				);
			}
		);
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException The part has not changed yet
	 */
	public function testSubscribingUnchangedPartWithError() {
		(new Subscribing\ChangedParts(
			new Subscribing\FakeParts()
		))->subscribe(
			new Subscribing\FakePart(null, $same = true),
			'www.google.com',
			'//h1',
			new Subscribing\FakeInterval()
		);
	}

	public function testIteratingChangedParts() {
		$parts = (new Subscribing\ChangedParts(
			new Subscribing\FakeParts(
				[
					new Subscribing\FakePart(
						null,
						$same = false,
						new Subscribing\FakePage(),
						new Subscribing\FakeExpression('//p')
					),
					new Subscribing\FakePart(
						null,
						$same = true,
						new Subscribing\FakePage(),
						new Subscribing\FakeExpression('//p')
					),
					new Subscribing\FakePart(
						null,
						$same = false,
						new Subscribing\FakePage(),
						new Subscribing\FakeExpression('//p')
					),
				]
			)
		))->iterate();
		Assert::count(2, $parts);
	}
}

(new ChangedParts())->run();
