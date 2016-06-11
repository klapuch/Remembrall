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

final class ExistingWebPage extends TestCase\Mockery {
	/**
	 * @throws \Remembrall\Exception\ExistenceException Web page with the "www.foo.xxx" address does not exist
	 */
	public function testNotFoundPage() {
		/** @var $response \Mockery\Mock */
		$response = $this->mockery('Psr\Http\Message\MessageInterface');
		$response->shouldReceive('getStatusCode')
			->once()
			->andReturn(404);
		/** @var $http \Mockery\Mock */
		$http = $this->mockery('GuzzleHttp\ClientInterface');
		$http->shouldReceive('request')
			->with('GET')
			->once()
			->andReturn($response);
		(new Subscribing\ExistingWebPage(
			new Subscribing\FakePage('www.foo.xxx'),
			$http
		))->content();
	}

	public function testFoundPage() {
		/** @var $response \Mockery\Mock */
		$response = $this->mockery('Psr\Http\Message\MessageInterface');
		$response->shouldReceive('getStatusCode')
			->once()
			->andReturn(200);
		/** @var $http \Mockery\Mock */
		$http = $this->mockery('GuzzleHttp\ClientInterface');
		$http->shouldReceive('request')
			->with('GET')
			->once()
			->andReturn($response);
		(new Subscribing\ExistingWebPage(
			new Subscribing\FakePage('http://www.google.com', new \DOMDocument),
			$http
		))->content();
		Assert::true(true);
	}
}

(new ExistingWebPage())->run();
