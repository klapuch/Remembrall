<?php
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Subscribing;

use Klapuch\{
	Uri, Log
};
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class LoggedParts extends TestCase\Mockery {
	/**
	 * @throws \DomainException exceptionMessage
	 */
	public function testLoggedExceptionDuringAdding() {
		$ex = new \DomainException('exceptionMessage');
		$parts = new Subscribing\FakeParts($ex);
		$logs = $this->mock(Log\Logs::class);
		$logs->shouldReceive('put')->once();
		(new Subscribing\LoggedParts(
			$parts, $logs
		))->add(new Subscribing\FakePart(), new Uri\FakeUri('url'), '//p');
	}

	public function testNoExceptionDuringAdding() {
		Assert::noError(
			function() {
				(new Subscribing\LoggedParts(
					new Subscribing\FakeParts(),
					new Log\FakeLogs()
				))->add(
					new Subscribing\FakePart(),
					new Uri\FakeUri('url'),
					'//p'
				);
			}
		);
	}

	/**
	 * @throws \DomainException exceptionMessage
	 */
	public function testLoggedExceptionDuringIterating() {
		$ex = new \DomainException('exceptionMessage');
		$parts = new Subscribing\FakeParts($ex);
		$logs = $this->mock(Log\Logs::class);
		$logs->shouldReceive('put')->once();
		(new Subscribing\LoggedParts($parts, $logs))->getIterator();
	}

	public function testNoExceptionDuringIterating() {
		Assert::noError(
			function() {
				(new Subscribing\LoggedParts(
					new Subscribing\FakeParts(),
					new Log\FakeLogs()
				))->getIterator();
			}
		);
	}
}

(new LoggedParts())->run();