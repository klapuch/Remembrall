<?php
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Subscribing;

use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class LoggedPage extends TestCase\Mockery {
	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringContent() {
		$ex = new \Exception('exceptionMessage');
		$parts = $this->mock(Subscribing\Page::class);
		$parts->shouldReceive('content')->andThrowExceptions([$ex]);
		$logger = $this->mock('Tracy\ILogger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Subscribing\LoggedPage($parts, $logger))->content();
	}

	public function testNoExceptionDuringContent() {
		Assert::noError(
			function() {
				$logger = $this->mock('Tracy\ILogger');
				(new Subscribing\LoggedPage(
					new Subscribing\FakePage(new \DOMDocument()), $logger
				))->content();
			}
		);
	}

	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringRefreshing() {
		$ex = new \Exception('exceptionMessage');
		$parts = $this->mock(Subscribing\Page::class);
		$parts->shouldReceive('refresh')->andThrowExceptions([$ex]);
		$logger = $this->mock('Tracy\ILogger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Subscribing\LoggedPage($parts, $logger))->refresh();
	}

	public function testNoExceptionDuringRefreshing() {
		Assert::noError(
			function() {
				$logger = $this->mock('Tracy\ILogger');
				(new Subscribing\LoggedPage(
					new Subscribing\FakePage(null, new Subscribing\FakePage()),
					$logger
				))->refresh();
			}
		);
	}
}

(new LoggedPage())->run();
