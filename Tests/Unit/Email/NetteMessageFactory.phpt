<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Email;

use Nette\Mail;
use Remembrall\Model\{
	Access, Email
};
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class NetteMessageFactory extends Tester\TestCase {
	public function testCreating() {
		$expectedMessage = (new Mail\Message())
			->setFrom('facedown@facedown.cz')
			->setSubject('fooSubject')
			->setBody('fooBody')
			->addBcc('foo@bar.cz')
			->addBcc('bar@foo.cz');
		Assert::equal(
			$expectedMessage,
			(new Email\NetteMessageFactory(
				new Email\FakeMessage(
					new Access\FakeSubscribers(
						[
							new Access\FakeSubscriber(1, 'foo@bar.cz'),
							new Access\FakeSubscriber(2, 'bar@foo.cz'),
						]
					),
					'facedown@facedown.cz',
					'fooSubject',
					'fooBody'
				)
			))->create()
		);
	}
}

(new NetteMessageFactory())->run();
