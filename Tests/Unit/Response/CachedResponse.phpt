<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Response;

use Klapuch\Application;
use Remembrall\Response;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class CachedResponse extends TestCase\Mockery {
	public function testCaching() {
		$origin = $this->mock(Application\Response::class);
		$origin->shouldReceive('body')->once();
		$origin->shouldReceive('headers')->once();
		$response = new Response\CachedResponse($origin);
		$body = $response->body();
		Assert::equal($body, $response->body());
		$headers = $response->headers();
		Assert::equal($headers, $response->headers());
	}
}

(new CachedResponse())->run();