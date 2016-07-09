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

final class LoggedPages extends TestCase\Mockery {
	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringAdding() {
		$ex = new \Exception('exceptionMessage');
		$parts = $this->mockery(Subscribing\Pages::class);
		$parts->shouldReceive('add')->andThrowExceptions([$ex]);
		$logger = $this->mockery('Tracy\ILogger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Subscribing\LoggedPages($parts, $logger))
			->add(new Subscribing\FakePage());
	}

	public function testNoExceptionDuringAdding() {
		Assert::noError(function() {
			$logger = $this->mockery('Tracy\ILogger');
			(new Subscribing\LoggedPages(
				new Subscribing\FakePages(), $logger
			))->add(new Subscribing\FakePage());
		});
	}

	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringIterating() {
		$ex = new \Exception('exceptionMessage');
		$parts = $this->mockery(Subscribing\Pages::class);
		$parts->shouldReceive('iterate')->andThrowExceptions([$ex]);
		$logger = $this->mockery('Tracy\ILogger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Subscribing\LoggedPages($parts, $logger))->iterate();
	}

	public function testNoExceptionDuringIterating() {
		Assert::noError(function() {
			$logger = $this->mockery('Tracy\ILogger');
			(new Subscribing\LoggedPages(
				new Subscribing\FakePages(), $logger
			))->iterate();
		});
	}

	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringReplacing() {
		$ex = new \Exception('exceptionMessage');
		$parts = $this->mockery(Subscribing\Pages::class);
		$parts->shouldReceive('replace')->andThrowExceptions([$ex]);
		$logger = $this->mockery('Tracy\ILogger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Subscribing\LoggedPages($parts, $logger))->replace(
			new Subscribing\FakePage(),
			new Subscribing\FakePage()
		);
	}

	public function testNoExceptionDuringReplacing() {
		Assert::noError(function() {
			$logger = $this->mockery('Tracy\ILogger');
			(new Subscribing\LoggedPages(
				new Subscribing\FakePages(), $logger
			))->replace(
				new Subscribing\FakePage(),
				new Subscribing\FakePage()
			);
		});
	}
}

(new LoggedPages())->run();
