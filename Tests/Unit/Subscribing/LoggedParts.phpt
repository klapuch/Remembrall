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
		$logger = $this->mockery('Tracy\Logger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Subscribing\LoggedParts($parts, $logger))
			->subscribe(
				new Subscribing\FakePart(),
				new Subscribing\FakeInterval()
			);
	}

	public function testNoExceptionDuringSubscribing() {
		Assert::noError(function() {
			$logger = $this->mockery('Tracy\Logger');
			(new Subscribing\LoggedParts(
				new Subscribing\FakeParts(), $logger
			))->subscribe(
				new Subscribing\FakePart(),
				new Subscribing\FakeInterval()
			);
		});
	}

	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringReplacing() {
		$ex = new \Exception('exceptionMessage');
		$parts = $this->mockery(Subscribing\Parts::class);
		$parts->shouldReceive('replace')->andThrowExceptions([$ex]);
		$logger = $this->mockery('Tracy\Logger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Subscribing\LoggedParts($parts, $logger))
			->replace(
				new Subscribing\FakePart(),
				new Subscribing\FakePart()
			);
	}

	public function testNoExceptionDuringReplacing() {
		Assert::noError(function() {
			$logger = $this->mockery('Tracy\Logger');
			(new Subscribing\LoggedParts(
				new Subscribing\FakeParts(), $logger
			))->replace(
				new Subscribing\FakePart(),
				new Subscribing\FakePart()
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
		$logger = $this->mockery('Tracy\Logger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Subscribing\LoggedParts($parts, $logger))
			->remove(
				new Subscribing\FakePart()
			);
	}

	public function testNoExceptionDuringRemoving() {
		Assert::noError(function() {
			$logger = $this->mockery('Tracy\Logger');
			(new Subscribing\LoggedParts(
				new Subscribing\FakeParts(), $logger
			))->remove(new Subscribing\FakePart());
		});
	}

	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringIterating() {
		$ex = new \Exception('exceptionMessage');
		$parts = $this->mockery(Subscribing\Parts::class);
		$parts->shouldReceive('iterate')->andThrowExceptions([$ex]);
		$logger = $this->mockery('Tracy\Logger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Subscribing\LoggedParts($parts, $logger))
			->iterate();
	}

	public function testNoExceptionDuringIterating() {
		Assert::noError(function() {
			$logger = $this->mockery('Tracy\Logger');
			(new Subscribing\LoggedParts(
				new Subscribing\FakeParts(), $logger
			))->iterate();
		});
	}
}

(new LoggedParts())->run();
