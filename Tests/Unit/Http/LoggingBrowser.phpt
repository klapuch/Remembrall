<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Http;

use Remembrall\Model\Http;
use Remembrall\TestCase;
use Tester\Assert;
use Tester;

require __DIR__ . '/../../bootstrap.php';

final class LoggingBrowser extends TestCase\Mockery {
	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringSending() {
		$ex = new \Exception('exceptionMessage');
		$browser = $this->mockery(Http\Browser::class);
		$browser->shouldReceive('send')->andThrowExceptions([$ex]);
		$logger = $this->mockery('Tracy\ILogger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Http\LoggingBrowser(
			$browser, $logger
		))->send(new Http\ConstantRequest(new Http\FakeHeaders()));
	}

	public function testNoExceptionDuringSending() {
		Assert::noError(function() {
			$logger = $this->mockery('Tracy\ILogger');
			(new Http\LoggingBrowser(
				new Http\FakeBrowser(new Http\FakeResponse()), $logger
			))->send(new Http\ConstantRequest(new Http\FakeHeaders()));
		});
	}
}

(new LoggingBrowser())->run();
