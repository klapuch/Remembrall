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

final class LoggedReports extends TestCase\Mockery {
	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringArchiving() {
		$ex = new \Exception('exceptionMessage');
		$parts = $this->mockery(Subscribing\Reports::class);
		$parts->shouldReceive('archive')->andThrowExceptions([$ex]);
		$logger = $this->mockery('Tracy\Logger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Subscribing\LoggedReports($parts, $logger))
			->archive(new Subscribing\FakePart());
	}

	public function testNoExceptionDuringArchiving() {
		Assert::noError(function() {
			$logger = $this->mockery('Tracy\Logger');
			(new Subscribing\LoggedReports(
				new Subscribing\FakeReports(), $logger
			))->archive(new Subscribing\FakePart());
		});
	}

	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringIterating() {
		$ex = new \Exception('exceptionMessage');
		$parts = $this->mockery(Subscribing\Reports::class);
		$parts->shouldReceive('iterate')->andThrowExceptions([$ex]);
		$logger = $this->mockery('Tracy\Logger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Subscribing\LoggedReports($parts, $logger))->iterate();
	}

	public function testNoExceptionDuringIterating() {
		Assert::noError(function() {
			$logger = $this->mockery('Tracy\Logger');
			(new Subscribing\LoggedReports(
				new Subscribing\FakeReports(), $logger
			))->iterate();
		});
	}
}

(new LoggedReports())->run();
