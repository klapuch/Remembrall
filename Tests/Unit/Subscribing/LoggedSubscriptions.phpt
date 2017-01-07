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
	public function testLoggingException() {
		$logs = $this->mock(Log\Logs::class);
		$logs->shouldReceive('put')->times(3);
		$subscriptions = new Subscribing\LoggedSubscriptions(
			new Subscribing\FakeSubscriptions(new \DomainException('fooMessage')),
			$logs
		);
		Assert::exception(function() use($subscriptions) {
			$subscriptions->subscribe(
				new Uri\FakeUri('url'),
				'//p',
				new Time\FakeInterval()
			);
		}, \DomainException::class, 'fooMessage');
		Assert::exception(function() use($subscriptions) {
			$subscriptions->print(new Output\FakeFormat());
		}, \DomainException::class, 'fooMessage');
		Assert::exception(function() use($subscriptions) {
			$subscriptions->getIterator();
		}, \DomainException::class, 'fooMessage');
	}

	public function testNoExceptionWithoutLogging() {
		$subscriptions = new Subscribing\LoggedSubscriptions(
			new Subscribing\FakeSubscriptions(),
			$this->mock(Log\Logs::class)
		);
		Assert::noError(function() use($subscriptions) {
			$subscriptions->subscribe(
				new Uri\FakeUri('url'),
				'//p',
				new Time\FakeInterval()
			);
		});
		Assert::noError(function() use($subscriptions) {
			$subscriptions->print(new Output\FakeFormat());
		});
		Assert::noError(function() use($subscriptions) {
			$subscriptions->getIterator();
		});
	}
}

(new LoggedSubscriptions())->run();