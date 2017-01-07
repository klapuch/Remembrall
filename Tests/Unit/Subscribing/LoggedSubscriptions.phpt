<?php
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Subscribing;

use Klapuch\{
	Output, Time, Uri, Log
};
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class LoggedSubscriptions extends TestCase\Mockery {
	/**
	 * @throws \DomainException exceptionMessage
	 */
	public function testLoggedExceptionDuringSubscribing() {
		$ex = new \DomainException('exceptionMessage');
		$logs = $this->mock(Log\Logs::class);
		$logs->shouldReceive('put')->once();
		(new Subscribing\LoggedSubscriptions(
			new Subscribing\FakeSubscriptions($ex),
			$logs
		))->subscribe(
			new Uri\FakeUri('url'),
			'//p',
			new Time\FakeInterval()
		);
	}

	public function testNoExceptionDuringSubscribing() {
		Assert::noError(
			function() {
				(new Subscribing\LoggedSubscriptions(
					new Subscribing\FakeSubscriptions(),
					new Log\FakeLogs()
				))->subscribe(
					new Uri\FakeUri('url'),
					'//p',
					new Time\FakeInterval()
				);
			}
		);
	}

	/**
	 * @throws \DomainException exceptionMessage
	 */
	public function testLoggedExceptionDuringPrinting() {
		$ex = new \DomainException('exceptionMessage');
		$logs = $this->mock(Log\Logs::class);
		$logs->shouldReceive('put')->once();
		(new Subscribing\LoggedSubscriptions(
			new Subscribing\FakeSubscriptions($ex),
			$logs
		))->print(new Output\FakeFormat());
	}

	public function testNoExceptionDuringPrinting() {
		Assert::noError(
			function() {
				(new Subscribing\LoggedSubscriptions(
					new Subscribing\FakeSubscriptions(),
					new Log\FakeLogs()
				))->print(new Output\FakeFormat());
			}
		);
	}

	/**
	 * @throws \DomainException exceptionMessage
	 */
	public function testLoggedExceptionDuringIterating() {
		$ex = new \DomainException('exceptionMessage');
		$logs = $this->mock(Log\Logs::class);
		$logs->shouldReceive('put')->once();
		(new Subscribing\LoggedSubscriptions(
			new Subscribing\FakeSubscriptions($ex),
			$logs
		))->getIterator();
	}

	public function testNoExceptionDuringIterating() {
		Assert::noError(
			function() {
				(new Subscribing\LoggedSubscriptions(
					new Subscribing\FakeSubscriptions(),
					new Log\FakeLogs()
				))->getIterator();
			}
		);
	}
}

(new LoggedSubscriptions())->run();