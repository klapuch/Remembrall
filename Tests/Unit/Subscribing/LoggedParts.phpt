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

final class LoggedParts extends TestCase\Mockery {
	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringSubscribing() {
		$ex = new \Exception('exceptionMessage');
		$parts = $this->mockery(Subscribing\Parts::class);
		$parts->shouldReceive('subscribe')->andThrowExceptions([$ex]);
		$logger = $this->mockery('Tracy\ILogger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Subscribing\LoggedParts($parts, $logger))
			->subscribe(
				new Subscribing\FakePart(),
				'url',
				'//p',
				new Subscribing\FakeInterval()
			);
	}

	public function testNoExceptionDuringSubscribing() {
		Assert::noError(function() {
			$logger = $this->mockery('Tracy\ILogger');
			(new Subscribing\LoggedParts(
				new Subscribing\FakeParts(), $logger
			))->subscribe(
				new Subscribing\FakePart(),
				'url',
				'//p',
				new Subscribing\FakeInterval()
			);
		});
	}

	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringRemoving() {
		$ex = new \Exception('exceptionMessage');
		$parts = $this->mockery(Subscribing\Parts::class);
		$parts->shouldReceive('remove')->andThrowExceptions([$ex]);
		$logger = $this->mockery('Tracy\ILogger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Subscribing\LoggedParts($parts, $logger))->remove('url', '//p');
	}

	public function testNoExceptionDuringRemoving() {
		Assert::noError(function() {
			$logger = $this->mockery('Tracy\ILogger');
			(new Subscribing\LoggedParts(
				new Subscribing\FakeParts(), $logger
			))->remove('url', '//p');
		});
	}

	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringIterating() {
		$ex = new \Exception('exceptionMessage');
		$parts = $this->mockery(Subscribing\Parts::class);
		$parts->shouldReceive('iterate')->andThrowExceptions([$ex]);
		$logger = $this->mockery('Tracy\ILogger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Subscribing\LoggedParts($parts, $logger))->iterate();
	}

	public function testNoExceptionDuringIterating() {
		Assert::noError(function() {
			$logger = $this->mockery('Tracy\ILogger');
			(new Subscribing\LoggedParts(
				new Subscribing\FakeParts(), $logger
			))->iterate();
		});
	}
}

(new LoggedParts())->run();
