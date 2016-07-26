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
	public function testAddingChangedPart() {
		Assert::noError(
			function() {
				(new Subscribing\ChangedParts(
					new Subscribing\FakeParts()
				))->add(
					new Subscribing\FakePart(null, $same = false),
					'www.google.com',
					'//h1'
				);
			}
		);
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException The part has not changed yet
	 */
	public function testAddingUnchangedPartWithError() {
		(new Subscribing\ChangedParts(
			new Subscribing\FakeParts()
		))->add(
			new Subscribing\FakePart(null, $same = true),
			'www.google.com',
			'//h1'
		);
	}

	public function testIteratingChangedParts() {
		$parts = (new Subscribing\ChangedParts(
			new Subscribing\FakeParts(
				[
					new Subscribing\FakePart(
						null,
						$same = false,
						'www.google.com',
						new Subscribing\FakeExpression('//p')
					),
					new Subscribing\FakePart(
						null,
						$same = true,
						'www.google.com',
						new Subscribing\FakeExpression('//p')
					),
					new Subscribing\FakePart(
						null,
						$same = false,
						'www.google.com',
						new Subscribing\FakeExpression('//p')
					),
				]
			)
		))->iterate();
		Assert::count(2, $parts);
	}
}

(new ChangedParts())->run();
