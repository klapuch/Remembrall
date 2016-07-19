<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Http;

use Remembrall\Model\{
	Http, Subscribing
};
use Remembrall\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class HtmlWebPage extends TestCase\Mockery {
	/**
	 * @throws \Remembrall\Exception\NotFoundException Web page must be HTML
	 */
	public function testCSSContentWithError() {
		(new Subscribing\HtmlWebPage(
			new Http\FakeResponse(
				new Http\FakeHeaders(['Content-Type' => 'text/css']), ''
			),
			new Http\FakeRequest()

		))->content();
	}

	public function testCorrectlyParsedHTMLContent() {
		Assert::same(
			'Hello Koňíčku',
			(new Subscribing\HtmlWebPage(
				new Http\FakeResponse(
					new Http\FakeHeaders(['Content-Type' => 'text/html']),
					'<html><p>Hello Koňíčku</p></html>'
				),
				new Http\FakeRequest()
			))->content()->getElementsByTagName('p')->item(0)->nodeValue
		);
	}

	public function testRefreshing() {
		$refreshPage = new Subscribing\FakePage();
		Assert::same(
			$refreshPage,
			(new Subscribing\HtmlWebPage(
				new Http\FakeResponse(),
				new Http\FakeRequest($refreshPage)
			))->refresh()
		);
	}
}

(new HtmlWebPage())->run();
