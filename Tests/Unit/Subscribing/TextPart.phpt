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

final class TextPart extends Tester\TestCase {
	public function testStrippedHTMLTags() {
		$part = new Subscribing\FakePart(
			'<p>Hi <span>there</span></p><div id="x"> Foo</div>'
		);
		Assert::same(
			'Hi there Foo',
			(new Subscribing\TextPart($part))->content()
		);
	}

	public function testSameContentButDifferentPage() {
		Assert::false(
			(new Subscribing\TextPart(
				new Subscribing\FakePart(
					'',
					new Subscribing\FakePage('google.com')
				)
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
			(new Subscribing\TextPart(
				new Subscribing\FakePart(
					'abc',
					new Subscribing\FakePage('google.com')
				)
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
			(new Subscribing\TextPart(
				new Subscribing\FakePart(
					'abc',
					new Subscribing\FakePage('google.com')
				)
			))->equals(
				new Subscribing\FakePart(
					'abc',
					new Subscribing\FakePage('google.com')
				)
			)
		);
	}
}

(new TextPart())->run();
