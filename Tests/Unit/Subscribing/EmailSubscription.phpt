<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Subscribing;

use Klapuch\Output;
use Nette\Mail;
use Remembrall\Model\Subscribing;
use Remembrall\Model\Web;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class EmailSubscription extends \Tester\TestCase {
	use TestCase\Mockery;

	public function testSendingAfterNotifying() {
		Assert::exception(
			function() {
				$mailer = $this->mock(Mail\IMailer::class);
				$mailer->shouldReceive('send')->never();
				(new Subscribing\EmailSubscription(
					new Subscribing\FakeSubscription(new \DomainException('foo')),
					$mailer,
					'recipient@foo.cz',
					new Web\FakePart()
				))->notify();
			},
			\DomainException::class,
			'foo'
		);
	}

	public function testTemplateOutput() {
		ob_start();
		(new Subscribing\EmailSubscription(
			new Subscribing\FakeSubscription(
				null,
				new Output\Xml(['url' => 'www.google.com', 'expression' => '//p', 'content' => 'FooBar'], 'part')
			),
			new class implements Mail\IMailer {
				public function send(Mail\Message $message) {
					printf(
						'To: %s',
						implode(array_keys($message->getHeader('To')))
					);
					printf('Subject: %s', $message->getSubject());
					printf('Body: %s', $message->getHtmlBody());
				}
			},
			'recipient@foo.cz'
		))->notify();
		$output = ob_get_clean();
		Assert::contains('To: recipient@foo.cz', $output);
		Assert::contains('Subject: Changes occurred on www.google.com page with //p expression', $output);
		Assert::contains('<p>Hi, there are some changes on <strong>www.google.com</strong> website with <strong>//p</strong> expression</p>', $output);
		Assert::contains('<p>Check it out bellow this text</p>', $output);
		Assert::contains('<br><p>FooBar</p>', $output);
		Assert::notContains('<?xml', $output);
	}
}

(new EmailSubscription())->run();