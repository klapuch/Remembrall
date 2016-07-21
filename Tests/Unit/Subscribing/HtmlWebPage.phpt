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

final class HtmlWebPage extends Tester\TestCase {
	public function testCorrectlyParsedHTMLContent() {
		Assert::same(
			'Hello Koňíčku',
			(new Subscribing\HtmlWebPage(
				new Http\FakeResponse(
					'<html><p>Hello Koňíčku</p></html>'
				),
				new Http\FakeRequest()
			))->content()->getElementsByTagName('p')->item(0)->nodeValue
		);
	}

	public function testRefreshing() {
		$fakePage = new Subscribing\FakePage();
		Assert::same(
			$fakePage,
			(new Subscribing\HtmlWebPage(
				new Http\FakeResponse(
					'<html><p>Hello Koňíčku</p></html>'
				),
				new Http\FakeRequest($fakePage)
			))->refresh()
		);
	}
}

(new HtmlWebPage())->run();
