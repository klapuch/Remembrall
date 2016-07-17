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
use Remembrall\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class WebBrowser extends Tester\TestCase {
	public function testHttpPage() {
		$http = new GuzzleHttp\Client();
		$headers = ['method' => 'get', 'host' => 'http://www.facedown.cz'];
		$page = (new Http\WebBrowser($http, new Subscribing\FakePages()))
			->send(new Http\ConstantRequest(new Http\FakeHeaders($headers)));
		Assert::type(Subscribing\AvailableWebPage::class, $page);
		Assert::same('http://www.facedown.cz', $page->url());
		$dom = Tester\DomQuery::fromHtml($page->content()->saveHTML());
		Assert::equal('Facedown', current($dom->find('h1')[0]));
	}

	public function testHttpsPage() {
		$http = new GuzzleHttp\Client();
		$headers = ['method' => 'get', 'host' => 'https://nette.org/'];
		$page = (new Http\WebBrowser($http, new Subscribing\FakePages()))
			->send(new Http\ConstantRequest(new Http\FakeHeaders($headers)));
		Assert::type(Subscribing\AvailableWebPage::class, $page);
		Assert::same('https://nette.org/', $page->url());
		$dom = Tester\DomQuery::fromHtml($page->content()->saveHTML());
		Assert::equal('Framework', current($dom->find('h1')[0]));
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException Connection could not be established. Does the URL really exist?
	 */
	public function testUnknownUrl() {
		$http = new GuzzleHttp\Client(['http_errors' => false]);
		$headers = ['method' => 'get', 'host' => 'http://www.čoromoro.xx'];
		(new Http\WebBrowser($http, new Subscribing\FakePages()))
			->send(new Http\ConstantRequest(new Http\FakeHeaders($headers)));
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException Connection could not be established. Does the URL really exist?
	 */
	public function testEmptyUrl() {
		$http = new GuzzleHttp\Client(['http_errors' => false]);
		$headers = ['method' => 'get', 'host' => 'http://www.čoromoro.xx'];
		(new Http\WebBrowser($http, new Subscribing\FakePages()))
			->send(new Http\ConstantRequest(new Http\FakeHeaders($headers)));
	}
}

(new WebBrowser())->run();
