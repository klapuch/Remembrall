<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Subscribing;

use Nette\Mail;
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class EmailSubscription extends TestCase\Mockery {
	public function testSendingAfterNotifying() {
		Assert::exception(function() {
			$mailer = $this->mock(Mail\IMailer::class);
			$mailer->shouldReceive('send')->never();
			(new Subscribing\EmailSubscription(
				new Subscribing\FakeSubscription(new \Exception('foo')),
				$mailer,
				new Mail\Message()
			))->notify();
		}, \Exception::class, 'foo');
	}

	public function testSendingWithoutModifiedMessage() {
		Assert::noError(function() {
			$message = new Mail\Message();
			$mailer = $this->mock(Mail\IMailer::class);
			$mailer->shouldReceive('send')
				->with($message)
				->once();
			(new Subscribing\EmailSubscription(
				new Subscribing\FakeSubscription(),
				$mailer,
				$message
			))->notify();
		});
	}
}

(new EmailSubscription())->run();
