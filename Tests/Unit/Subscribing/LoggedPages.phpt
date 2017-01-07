<?php
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Subscribing;

use Klapuch\{
	Log, Uri
};
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class LoggedPages extends TestCase\Mockery {
	/**
	 * @throws \DomainException fooMessage
	 */
	public function testLoggingOnThrowing() {
		$pages = $this->mock(Subscribing\Pages::class);
		$pages->shouldReceive('add')->andThrowExceptions(
			[new \DomainException('fooMessage')]
		);
		$logs = $this->mock(Log\Logs::class);
		$logs->shouldReceive('put')->once();
		(new Subscribing\LoggedPages($pages, $logs))->add(
			new Uri\FakeUri(),
			new Subscribing\FakePage()
		);
	}

	public function testNoExceptionWithoutLogging() {
		Assert::noError(function() {
			(new Subscribing\LoggedPages(
				new Subscribing\FakePages(),
				$this->mock(Log\Logs::class)
			))->add(new Uri\FakeUri(), new Subscribing\FakePage());
		});
	}
}

(new LoggedPages())->run();