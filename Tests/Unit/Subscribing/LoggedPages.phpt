<?php
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Subscribing;

use Klapuch\{
	Log, Uri
};
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class LoggedPages extends TestCase\Mockery {
	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringAdding() {
		$ex = new \Exception('exceptionMessage');
		$pages = $this->mock(Subscribing\Pages::class);
		$pages->shouldReceive('add')->andThrowExceptions([$ex]);
		$logs = $this->mock(Log\Logs::class);
		$logs->shouldReceive('put')->once();
		(new Subscribing\LoggedPages($pages, $logs))->add(
			new Uri\FakeUri(),
			new Subscribing\FakePage()
		);
	}

	public function testNoExceptionDuringAdding() {
		Assert::noError(
			function() {
				(new Subscribing\LoggedPages(
					new Subscribing\FakePages(),
					new Log\FakeLogs()
				))->add(new Uri\FakeUri(), new Subscribing\FakePage());
			}
		);
	}
}

(new LoggedPages())->run();