<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Http;

use GuzzleHttp;
use Remembrall\Model\{
	Http, Subscribing
};
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class DefaultRequest extends Tester\TestCase {
	public function testHttpPage() {
		$http = new GuzzleHttp\Client(['http_errors' => false]);
		$page = (new Http\DefaultRequest(
			$http,
			new GuzzleHttp\Psr7\Request('GET', 'http://www.facedown.cz')
		))->send();
		Assert::type(Subscribing\HtmlWebPage::class, $page);
		$dom = Tester\DomQuery::fromHtml($page->content()->saveHTML());
		Assert::equal('Facedown', current($dom->find('h1')[0]));
	}

	public function testHttpsPage() {
		$http = new GuzzleHttp\Client(['http_errors' => false]);
		$page = (new Http\DefaultRequest(
			$http,
			new GuzzleHttp\Psr7\Request('GET', 'https://nette.org/')
		))->send();
		Assert::type(Subscribing\HtmlWebPage::class, $page);
		$dom = Tester\DomQuery::fromHtml($page->content()->saveHTML());
		Assert::equal('Framework', current($dom->find('h1')[0]));
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException Page could not be retrieved. Does the URL really exist?
	 */
	public function testUnknownUrl() {
		$http = new GuzzleHttp\Client(['http_errors' => false]);
		(new Http\DefaultRequest(
			$http,
			new GuzzleHttp\Psr7\Request('GET', 'http://www.čoromoro.xx')
		))->send();
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException Page could not be retrieved. Does the URL really exist?
	 */
	public function testEmptyUrl() {
		$http = new GuzzleHttp\Client(['http_errors' => false]);
		(new Http\DefaultRequest(
			$http,
			new GuzzleHttp\Psr7\Request('GET', '')
		))->send();
	}
}

(new DefaultRequest())->run();
