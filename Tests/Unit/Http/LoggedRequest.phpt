<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Http;

use Remembrall\Model\{
	Http, Subscribing
};
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class LoggedRequest extends TestCase\Mockery {
	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringSending() {
		$ex = new \Exception('exceptionMessage');
		$browser = $this->mockery(Http\Request::class);
		$browser->shouldReceive('send')->andThrowExceptions([$ex]);
		$logger = $this->mockery('Tracy\ILogger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Http\LoggedRequest(
			$browser, $logger
		))->send();
	}

	public function testNoExceptionDuringSending() {
		Assert::noError(function() {
			$logger = $this->mockery('Tracy\ILogger');
			(new Http\LoggedRequest(
				new Http\FakeRequest(new Subscribing\FakePage), $logger
			))->send();
		});
	}
}

(new LoggedRequest())->run();
