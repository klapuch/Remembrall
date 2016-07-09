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
	public function testValidUrl() {
		$url = 'http://www.google.com';
		Assert::same(
			$url,
			(new Subscribing\HtmlWebPage(
				new Http\ConstantRequest(
					new Http\FakeHeaders(['host' => $url])
				),
				new Http\FakeResponse()
			))->url()
		);
	}

	public function testInvalidUrlWithoutError() {
		$url = 'fooBar';
		Assert::same(
			$url,
			(new Subscribing\HtmlWebPage(
				new Http\ConstantRequest(
					new Http\FakeHeaders(['host' => $url])
				),
				new Http\FakeResponse()
			))->url()
		);
	}

	/**
	 * @throws \Remembrall\Exception\ExistenceException Web page must be HTML
	 */
	public function testCSSContentWithError() {
		(new Subscribing\HtmlWebPage(
			new Http\ConstantRequest(new Http\FakeHeaders()),
			new Http\FakeResponse(
				new Http\FakeHeaders(['Content-Type' => 'text/css']), ''
			)
		))->content();
	}

	public function testCorrectlyParsedHTMLContent() {
		Assert::same(
			'Hello Koňíčku',
			(new Subscribing\HtmlWebPage(
				new Http\ConstantRequest(new Http\FakeHeaders()),
				new Http\FakeResponse(
					new Http\FakeHeaders(['Content-Type' => 'text/html']),
					'<html><p>Hello Koňíčku</p></html>'
				)
			))->content()->getElementsByTagName('p')->item(0)->nodeValue
		);
	}

	protected function differentPages() {
		return [
			['www.google.com', 'www.google.cz'],
			['www.google.com', 'http://www.google.cz'],
			['www.google.com', 'www.Google.com'],
			['https://www.google.com', 'http://www.google.com'],
		];
	}

	public function testEquivalentPages() {
		$url = 'www.google.com';
		Assert::true(
			(new Subscribing\HtmlWebPage(
				new Http\ConstantRequest(new Http\FakeHeaders(['host' => $url])),
				new Http\FakeResponse()
			))->equals(new Subscribing\FakePage($url))
		);
	}

	/**
	 * @dataProvider differentPages
	 */
	public function testDifferentPages($actual, $difference) {
		Assert::false(
			(new Subscribing\HtmlWebPage(
				new Http\ConstantRequest(new Http\FakeHeaders(['host' => $actual])),
				new Http\FakeResponse()
			))->equals(new Subscribing\FakePage($difference))
		);
	}
}

(new HtmlWebPage())->run();
