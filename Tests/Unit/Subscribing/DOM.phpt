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

final class DOM extends Tester\TestCase {
	public function testUtf8Encoding() {
		$dom = new Subscribing\DOM();
		$dom->loadHTML('<p>Příliš žluťoučký kůň úpěl ďábelské ódy.</p>');
		Assert::same(
			'Příliš žluťoučký kůň úpěl ďábelské ódy.',
			$dom->getElementsByTagName('p')->item(0)->nodeValue
		);
	}

	public function testSuppressedWarningOnInvalidHtml() {
		Assert::noError(
			function() {
				(new Subscribing\DOM())->loadHTML(
					'<a href="script.php?foo=bar&hello=world">link</a>'
				);
			}
		);
	}
}

(new DOM())->run();
