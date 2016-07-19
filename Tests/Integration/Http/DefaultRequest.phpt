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

final class DefaultRequest extends Tester\TestCase {
	public function testHttpPage() {
		$http = new GuzzleHttp\Client();
		$headers = ['method' => 'get', 'host' => 'http://www.facedown.cz'];
		$page = (new Http\DefaultRequest(
			$http,
			new Http\FakeHeaders($headers)
		))->send();
		Assert::type(Subscribing\AvailableWebPage::class, $page);
		$dom = Tester\DomQuery::fromHtml($page->content()->saveHTML());
		Assert::equal('Facedown', current($dom->find('h1')[0]));
	}

	public function testHttpsPage() {
		$http = new GuzzleHttp\Client();
		$headers = ['method' => 'get', 'host' => 'https://nette.org/'];
		$page = (new Http\DefaultRequest(
			$http,
			new Http\FakeHeaders($headers)
		))->send();
		Assert::type(Subscribing\AvailableWebPage::class, $page);
		$dom = Tester\DomQuery::fromHtml($page->content()->saveHTML());
		Assert::equal('Framework', current($dom->find('h1')[0]));
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException Page could not be retrieved. Does the URL really exist?
	 */
	public function testUnknownUrl() {
		$http = new GuzzleHttp\Client(['http_errors' => false]);
		$headers = ['method' => 'get', 'host' => 'http://www.čoromoro.xx'];
		(new Http\DefaultRequest(
			$http,
			new Http\FakeHeaders($headers)
		))->send();
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException Page could not be retrieved. Does the URL really exist?
	 */
	public function testEmptyUrl() {
		$http = new GuzzleHttp\Client(['http_errors' => false]);
		$headers = ['method' => 'get', 'host' => 'http://www.čoromoro.xx'];
		(new Http\DefaultRequest(
			$http,
			new Http\FakeHeaders($headers)
		))->send();
	}
}

(new DefaultRequest())->run();
