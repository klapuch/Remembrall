<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Subscribing;

use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;
use Tester;

require __DIR__ . '/../../bootstrap.php';

final class LoggedSubscriptions extends TestCase\Mockery {
	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringSubscribing() {
		$ex = new \Exception('exceptionMessage');
		$subscriptions = $this->mockery(Subscribing\Subscriptions::class);
		$subscriptions->shouldReceive('subscribe')->andThrowExceptions([$ex]);
		$logger = $this->mockery('Tracy\ILogger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Subscribing\LoggedSubscriptions($subscriptions, $logger))
			->subscribe(
				'url',
				'//p',
				new Subscribing\FakeInterval()
			);
	}

	public function testNoExceptionDuringSubscribing() {
		Assert::noError(function() {
			$logger = $this->mockery('Tracy\ILogger');
			(new Subscribing\LoggedSubscriptions(
				new Subscribing\FakeSubscriptions(), $logger
			))->subscribe(
				'url',
				'//p',
				new Subscribing\FakeInterval()
			);
		});
	}

	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringIterating() {
		$ex = new \Exception('exceptionMessage');
		$subscriptions = $this->mockery(Subscribing\Subscriptions::class);
		$subscriptions->shouldReceive('iterate')->andThrowExceptions([$ex]);
		$logger = $this->mockery('Tracy\ILogger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Subscribing\LoggedSubscriptions($subscriptions, $logger))->iterate();
	}

	public function testNoExceptionDuringIterating() {
		Assert::noError(function() {
			$logger = $this->mockery('Tracy\ILogger');
			(new Subscribing\LoggedSubscriptions(
				new Subscribing\FakeSubscriptions(), $logger
			))->iterate();
		});
	}
}

(new LoggedSubscriptions())->run();
