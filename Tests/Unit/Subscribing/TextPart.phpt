<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Subscribing;

use Remembrall\Model\Subscribing;
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
}

(new TextPart())->run();
