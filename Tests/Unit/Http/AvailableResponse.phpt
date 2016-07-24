<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Http;

use GuzzleHttp;
use Remembrall\Model\Http;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class AvailableResponse extends TestCase\Mockery {
	/**
	 * @throws \Remembrall\Exception\NotFoundException Content could not be retrieved because of "404 Not Found"
	 */
	public function testNotFoundPage() {
		(new Http\AvailableResponse(
			new GuzzleHttp\Psr7\Response(404)
		))->content();
	}

	public function testFoundPage() {
		Assert::same(
			'there is some body',
			(new Http\AvailableResponse(
				new GuzzleHttp\Psr7\Response(200, [], 'there is some body')
			))->content()
		);
	}
}

(new AvailableResponse())->run();
