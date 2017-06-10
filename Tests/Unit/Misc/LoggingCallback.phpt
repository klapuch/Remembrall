<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Misc;

use Klapuch\Log;
use Remembrall\Model\Misc;
use Remembrall\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class LoggingCallback extends \Tester\TestCase {
	use TestCase\Mockery;

	public function testLoggingOnThrowing() {
		$file = Tester\FileMock::create('');
		Assert::exception(function() use ($file) {
			(new Misc\LoggingCallback(new Log\FakeLogs($file)))->invoke(function() {
				throw new \DomainException('fooMessage');
			});
		}, new \DomainException, 'fooMessage');
		Assert::contains('fooMessage', file_get_contents($file));
	}

	public function testNoExceptionWithoutLogging() {
		Assert::noError(function() {
			(new Misc\LoggingCallback(
				$this->mock(Log\Logs::class)
			))->invoke('strlen', ['abc']);
		});
	}

	public function testReturningValue() {
		Assert::same(
			3,
			(new Misc\LoggingCallback(
				$this->mock(Log\Logs::class)
			))->invoke('strlen', ['abc'])
		);
	}
}

(new LoggingCallback())->run();