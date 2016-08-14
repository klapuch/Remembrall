<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Http;

use Remembrall\Model\{
	Access, Email, Subscribing
};
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class SubscribingMessage extends TestCase\Database {
	public function testSender() {
		Assert::same(
			'Remembrall <remembrall@remembrall.org>',
			(new Email\SubscribingMessage(
				new Subscribing\FakePart(
					null,
					'url',
					null,
					new Subscribing\FakeExpression('//p')
				),
				$this->database
			))->sender()
		);
	}

	public function testRecipients() {
		Assert::equal(
			new Access\OutdatedSubscribers(
				new Access\FakeSubscribers(),
				'url',
				'//p',
				$this->database
			),
			(new Email\SubscribingMessage(
				new Subscribing\FakePart(
					null,
					'url',
					null,
					new Subscribing\FakeExpression('//p')
				),
				$this->database
			))->recipients()
		);
	}

	public function testSubject() {
		Assert::same(
			'Changes occurred on "www.google.com" page with "//h1" expression',
			(new Email\SubscribingMessage(
				new Subscribing\FakePart(
					null,
					'www.google.com',
					null,
					new Subscribing\FakeExpression('//h1')
				),
				$this->database
			))->subject()
		);
	}

	public function testContent() {
		$content = (new Email\SubscribingMessage(
			new Subscribing\FakePart(
				'<p>I don\'t know</p>',
				'www.google.com',
				null,
				new Subscribing\FakeExpression('//h1')
			),
			$this->database
		))->content();
		Assert::contains('www.google.com', $content);
		Assert::contains('//h1', $content);
		Assert::contains("<p>&lt;p&gt;I don't know&lt;/p&gt;</p>", $content);
	}
}

(new SubscribingMessage())->run();
