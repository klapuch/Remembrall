<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Http;

use Dibi;
use Latte;
use Nette\Application\UI;
use Nette\Bridges\ApplicationLatte;
use Remembrall\Model\{
	Access, Email, Subscribing
};
use Remembrall\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class SubscribingMessage extends TestCase\Database {
	public function testSender() {
		Assert::same(
			'Remembrall <remembrall@remembrall.org>',
			(new Email\SubscribingMessage(
				new Subscribing\FakePart(),
				'url',
				'//p',
				new class implements UI\ITemplateFactory {
					function createTemplate(UI\Control $control = null) {
					}
				},
				$this->database
			))->sender()
		);
	}

	public function testRecipients() {
		Assert::equal(
			new Access\PartSharedSubscribers(
				new Access\FakeSubscribers(),
				'url',
				'//p',
				$this->database
			),
			(new Email\SubscribingMessage(
				new Subscribing\FakePart(),
				'url',
				'//p',
				new class implements UI\ITemplateFactory {
					function createTemplate(UI\Control $control = null) {
					}
				},
				$this->database
			))->recipients()
		);
	}

	public function testSubject() {
		Assert::same(
			'Changes occurred on "www.google.com" page with "//h1" expression',
			(new Email\SubscribingMessage(
				new Subscribing\FakePart(),
				'www.google.com',
				'//h1',
				new class implements UI\ITemplateFactory {
					function createTemplate(UI\Control $control = null) {
					}
				},
				$this->database
			))->subject()
		);
	}

	public function testContent() {
		$content = (new Email\SubscribingMessage(
			new Subscribing\FakePart('fooContent'),
			'www.google.com',
			'//h1',
			new class implements UI\ITemplateFactory {
				function createTemplate(UI\Control $control = null) {
					return (new ApplicationLatte\TemplateFactory(
						new class implements ApplicationLatte\ILatteFactory {
							public function create() {
								return new Latte\Engine();
							}
						}
					))->createTemplate($control);
				}
			},
			$this->database
		))->content();
		Assert::contains('www.google.com', $content);
		Assert::contains('//h1', $content);
		Assert::contains('fooContent', $content);
	}
}

(new SubscribingMessage())->run();
