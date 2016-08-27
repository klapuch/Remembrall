<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use GuzzleHttp;
use Psr\Http\Message\ResponseInterface;
use Remembrall\Model\Subscribing;
use Tester;
use Tester\Assert;
use Remembrall\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class HtmlWebPage extends TestCase\Mockery {
	public function testHttpPageContentWithoutError() {
		$content = (new Subscribing\HtmlWebPage(
			'http://www.facedown.cz',
			new GuzzleHttp\Client(['http_errors' => false])
		))->content();
		$dom = Tester\DomQuery::fromHtml($content->saveHTML());
		Assert::equal('Facedown', current($dom->find('h1')[0]));
	}

	public function testHttpsPage() {
		$content = (new Subscribing\HtmlWebPage(
			'https://nette.org/',
			new GuzzleHttp\Client(['http_errors' => false])
		))->content();
		$dom = Tester\DomQuery::fromHtml($content->saveHTML());
		Assert::equal('Framework', current($dom->find('h1')[0]));
    }

    public function testHttpPageWithExactlyContentTypeMatchWithoutError() {
        Assert::noError(function() {
           (new Subscribing\HtmlWebPage(
                'http://www.example.com',
                new GuzzleHttp\Client(['http_errors' => false])
            ))->content();
        });
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException Page "http://www.example.com" is not in HTML format
	 */
	public function testHttpPageWithEmptyContentType() {
		$request = $this->mockery(GuzzleHttp\ClientInterface::class);
		$response = $this->mockery(ResponseInterface::class);
		$response->shouldReceive('getHeader')
			->with('Content-Type')
			->andReturn([]);
		$response->shouldReceive('getStatusCode')
			->andReturn(0);
		$request->shouldReceive('send')
			->andReturn($response);
		(new Subscribing\HtmlWebPage(
			'http://www.example.com',
			$request
		))->content();
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException Content could not be retrieved because of "404 Not Found"
	 */
	public function testWithErrorStatusCode() {
		(new Subscribing\HtmlWebPage(
			'https://www.google.cz/404',
			new GuzzleHttp\Client(['http_errors' => false])
		))->content();
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException Page "https://www.google.com/sitemap.xml" is not in HTML format
	 */
	public function testXmlPageWithError() {
		(new Subscribing\HtmlWebPage(
			'https://www.google.com/sitemap.xml',
			new GuzzleHttp\Client(['http_errors' => false])
		))->content();
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException Page "http://www.čoromoro.xx" is unreachable. Does the URL exist?
	 */
	public function testUnknownUrl() {
		(new Subscribing\HtmlWebPage(
			'http://www.čoromoro.xx',
			new GuzzleHttp\Client(['http_errors' => false])
		))->content();
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException Page "" is unreachable. Does the URL exist?
	 */
	public function testEmptyUrl() {
		(new Subscribing\HtmlWebPage(
			'',
			new GuzzleHttp\Client(['http_errors' => false])
		))->content();
	}

	public function testRefreshing() {
		$page = new Subscribing\HtmlWebPage(
			'whatever',
			new GuzzleHttp\Client(['http_errors' => false])
		);
		Assert::notSame($page, $page->refresh());
	}
}

(new HtmlWebPage())->run();
