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

final class WebBrowser extends TestCase\Database {
	public function testHttpPage() {
		$http = new GuzzleHttp\Client();
		$headers = ['method' => 'get', 'host' => 'http://www.facedown.cz'];
		$page = (new Http\WebBrowser($http, $this->database))->send(
			new Http\ConstantRequest(new Http\FakeHeaders($headers)));
		Assert::type(Subscribing\AvailableWebPage::class, $page);
		$pages = $this->database->fetchAll('SELECT * FROM pages');
		Assert::count(1, $pages);
		Assert::same('http://www.facedown.cz', $pages[0]['url']);
		$dom = Tester\DomQuery::fromHtml($pages[0]['content']);
		Assert::equal('Facedown', current($dom->find('h1')[0]));
		$visits = $this->database->fetchAll('SELECT * FROM page_visits');
		Assert::count(1, $visits);
		Assert::same('http://www.facedown.cz', $visits[0]['page_url']);
	}

	public function testHttpsPage() {
		$http = new GuzzleHttp\Client();
		$headers = ['method' => 'get', 'host' => 'https://nette.org/'];
		$page = (new Http\WebBrowser($http, $this->database))->send(
			new Http\ConstantRequest(new Http\FakeHeaders($headers)));
		Assert::type(Subscribing\AvailableWebPage::class, $page);
		$pages = $this->database->fetchAll('SELECT * FROM pages');
		Assert::count(1, $pages);
		Assert::same('https://nette.org/', $pages[0]['url']);
		$dom = Tester\DomQuery::fromHtml($pages[0]['content']);
		Assert::equal('Framework', current($dom->find('h1')[0]));
		$visits = $this->database->fetchAll('SELECT * FROM page_visits');
		Assert::count(1, $visits);
		Assert::same('https://nette.org/', $visits[0]['page_url']);
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException Connection could not be established. Does the URL really exist?
	 */
	public function testUnknownUrl() {
		$http = new GuzzleHttp\Client(['http_errors' => false]);
		$headers = ['method' => 'get', 'host' => 'http://www.čoromoro.xx'];
		(new Http\WebBrowser($http, $this->database))->send(
			new Http\ConstantRequest(new Http\FakeHeaders($headers)));
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException Connection could not be established. Does the URL really exist?
	 */
	public function testEmptyUrl() {
		$http = new GuzzleHttp\Client(['http_errors' => false]);
		$headers = ['method' => 'get', 'host' => 'http://www.čoromoro.xx'];
		(new Http\WebBrowser($http, $this->database))->send(
			new Http\ConstantRequest(new Http\FakeHeaders($headers)));
	}

	protected function prepareDatabase() {
		$this->database->query('TRUNCATE pages');
		$this->database->query('TRUNCATE page_visits');
	}
}

(new WebBrowser())->run();
