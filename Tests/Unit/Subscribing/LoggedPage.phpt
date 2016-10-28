<?php
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Subscribing;

use Klapuch\Log;
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
		$page = $this->mock(Subscribing\Page::class);
		$page->shouldReceive('content')->andThrowExceptions([$ex]);
		$logs = $this->mock(Log\Logs::class);
		$logs->shouldReceive('put')->once();
		(new Subscribing\LoggedPage($page, $logs))->content();
	}

	public function testNoExceptionDuringContent() {
		Assert::noError(
			function() {
				(new Subscribing\LoggedPage(
					new Subscribing\FakePage(new \DOMDocument()),
					new Log\FakeLogs()
				))->content();
			}
		);
	}

	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringRefreshing() {
		$ex = new \Exception('exceptionMessage');
		$page = $this->mock(Subscribing\Page::class);
		$page->shouldReceive('refresh')->andThrowExceptions([$ex]);
		$logs = $this->mock(Log\Logs::class);
		$logs->shouldReceive('put')->once();
		(new Subscribing\LoggedPage($page, $logs))->refresh();
	}

	public function testNoExceptionDuringRefreshing() {
		Assert::noError(
			function() {
				(new Subscribing\LoggedPage(
					new Subscribing\FakePage(null, new Subscribing\FakePage()),
					new Log\FakeLogs()
				))->refresh();
			}
		);
	}
}

(new LoggedPage())->run();
