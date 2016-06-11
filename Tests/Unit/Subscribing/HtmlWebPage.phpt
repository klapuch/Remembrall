<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Subscribing;

use Tester,
	Tester\Assert;
use Remembrall\TestCase;
use Remembrall\Model\Subscribing;

require __DIR__ . '/../../bootstrap.php';

final class HtmlWebPage extends TestCase\Mockery {
	public function testValidUrl() {
		$url = 'http://www.google.com';
		/** @var $http \Mockery\Mock */
		$http = $this->mockery('GuzzleHttp\ClientInterface');
		$http->shouldReceive('getConfig')
			->with('base_uri')
			->once()
			->andReturn($url);
		Assert::same($url, (new Subscribing\HtmlWebPage($http))->url());
	}

	public function testInvalidUrlWithoutError() {
		$url = 'fooBar';
		/** @var $http \Mockery\Mock */
		$http = $this->mockery('GuzzleHttp\ClientInterface');
		$http->shouldReceive('getConfig')
			->with('base_uri')
			->once()
			->andReturn($url);
		Assert::same($url, (new Subscribing\HtmlWebPage($http))->url());
	}

	/**
	 * @throws \Remembrall\Exception\ExistenceException Web page must be HTML
	 */
	public function testCSSContentWithError() {
		/** @var $response \Mockery\Mock */
		$response = $this->mockery('Psr\Http\Message\MessageInterface');
		$response->shouldReceive('getHeader')
			->with('Content-Type')
			->once()
			->andReturn('text/css');
		/** @var $http \Mockery\Mock */
		$http = $this->mockery('GuzzleHttp\ClientInterface');
		$http->shouldReceive('request')
			->with('GET')
			->once()
			->andReturn($response);
		(new Subscribing\HtmlWebPage($http))->content();
	}

	public function testCorrectlyParsedHTMLContent() {
		/** @var $response \Mockery\Mock */
		$response = $this->mockery('Psr\Http\Message\MessageInterface');
		$response->shouldReceive('getHeader')
			->with('Content-Type')
			->once()
			->andReturn('text/HTML');
		$response->shouldReceive('getBody')
			->once()
			->andReturn('<html><p>Hello</p></html>');
		/** @var $http \Mockery\Mock */
		$http = $this->mockery('GuzzleHttp\ClientInterface');
		$http->shouldReceive('request')
			->with('GET')
			->once()
			->andReturn($response);
		$dom = (new Subscribing\HtmlWebPage($http))->content();
		Assert::same('Hello', $dom->getElementsByTagName('p')->item(0)->nodeValue);
	}
}

(new HtmlWebPage())->run();
