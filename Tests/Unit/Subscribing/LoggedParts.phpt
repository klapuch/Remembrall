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
	public function testLoggedExceptionDuringAdding() {
		$ex = new \Exception('exceptionMessage');
		$parts = $this->mockery(Subscribing\Parts::class);
		$parts->shouldReceive('add')->andThrowExceptions([$ex]);
		$logger = $this->mockery('Tracy\ILogger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Subscribing\LoggedParts($parts, $logger))
			->add(
				new Subscribing\FakePart(),
				'url',
				'//p'
			);
	}

	public function testNoExceptionDuringAdd() {
		Assert::noError(function() {
			$logger = $this->mockery('Tracy\ILogger');
			(new Subscribing\LoggedParts(
				new Subscribing\FakeParts(), $logger
			))->add(
				new Subscribing\FakePart(),
				'url',
				'//p'
			);
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
