<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Subscribing;

use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;
use Klapuch\Uri;

require __DIR__ . '/../../bootstrap.php';

final class LoggedParts extends TestCase\Mockery {
	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringAdding() {
		$ex = new \Exception('exceptionMessage');
		$parts = new Subscribing\FakeParts($ex);
		$logger = $this->mock('Tracy\ILogger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Subscribing\LoggedParts(
			$parts, $logger
		))->add(new Subscribing\FakePart(), new Uri\FakeUri('url'), '//p');
	}

	public function testNoExceptionDuringAdding() {
		Assert::noError(function() {
			$logger = $this->mock('Tracy\ILogger');
			(new Subscribing\LoggedParts(
				new Subscribing\FakeParts(), $logger
			))->add(new Subscribing\FakePart(), new Uri\FakeUri('url'), '//p');
		});
	}

	/**
	 * @throws \Exception exceptionMessage
	 */
	public function testLoggedExceptionDuringIterating() {
		$ex = new \Exception('exceptionMessage');
		$parts = new Subscribing\FakeParts($ex);
		$logger = $this->mock('Tracy\ILogger');
		$logger->shouldReceive('log')->once()->with($ex, 'error');
		(new Subscribing\LoggedParts($parts, $logger))->iterate();
	}

	public function testNoExceptionDuringIterating() {
		Assert::noError(function() {
			$logger = $this->mock('Tracy\ILogger');
			(new Subscribing\LoggedParts(
				new Subscribing\FakeParts(), $logger
			))->iterate();
		});
	}
}

(new LoggedParts())->run();
