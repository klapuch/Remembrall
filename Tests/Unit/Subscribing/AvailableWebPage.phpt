<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Subscribing;

use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class AvailableWebPage extends TestCase\Mockery {
	/**
	 * @throws \Remembrall\Exception\ExistenceException Web page "www.foo.xxx" can not be loaded because of 404 - Not Found
	 */
	public function testNotFoundPage() {
		/** @var $response \Mockery\Mock */
		$response = $this->mockery('Psr\Http\Message\ResponseInterface');
		$response->shouldReceive('getStatusCode')
			->twice()
			->andReturn(404);
		$response->shouldReceive('getReasonPhrase')
			->once()
			->andReturn('Not Found');
		/** @var $http \Mockery\Mock */
		$http = $this->mockery('GuzzleHttp\ClientInterface');
		$http->shouldReceive('request')
			->with('GET')
			->once()
			->andReturn($response);
		(new Subscribing\AvailableWebPage(
			new Subscribing\FakePage('www.foo.xxx'),
			$http
		))->content();
	}

	public function testFoundPage() {
		/** @var $response \Mockery\Mock */
		$response = $this->mockery('Psr\Http\Message\ResponseInterface');
		$response->shouldReceive('getStatusCode')
			->once()
			->andReturn(200);
		/** @var $http \Mockery\Mock */
		$http = $this->mockery('GuzzleHttp\ClientInterface');
		$http->shouldReceive('request')
			->with('GET')
			->once()
			->andReturn($response);
		(new Subscribing\AvailableWebPage(
			new Subscribing\FakePage('http://www.google.com', new \DOMDocument),
			$http
		))->content();
		Assert::true(true);
	}
}

(new AvailableWebPage())->run();
