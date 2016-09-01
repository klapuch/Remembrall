<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Subscribing;

use Remembrall\Model\Subscribing;
use Tester;
use Tester\Assert;
use Klapuch\Uri;

require __DIR__ . '/../../bootstrap.php';

final class ChangedParts extends Tester\TestCase {
	public function testAddingChangedPart() {
		Assert::noError(
			function() {
				(new Subscribing\ChangedParts(
					new Subscribing\FakeParts()
				))->add(
					new Subscribing\FakePart(
						'abc',
						null,
						new Subscribing\FakePart('xxx')
					),
					new Uri\FakeUri('www.google.com'),
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
			new Subscribing\FakePart(
				'abc',
				null,
				new Subscribing\FakePart('abc')
			),
			new Uri\FakeUri('www.google.com'),
			'//h1'
		);
	}

	public function testIteratingChangedParts() {
		$parts = (new Subscribing\ChangedParts(
			new Subscribing\FakeParts(
				[
					new Subscribing\FakePart(
						'abc',
						'www.google.com',
						new Subscribing\FakePart('xxx')
					),
					new Subscribing\FakePart(
						'abc',
						'www.google.com',
						new Subscribing\FakePart('abc')
					),
					new Subscribing\FakePart(
						'def',
						'www.google.com',
						new Subscribing\FakePart('xxx')
					),
				]
			)
		))->iterate();
		Assert::count(2, $parts);
	}
}

(new ChangedParts())->run();
