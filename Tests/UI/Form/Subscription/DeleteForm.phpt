<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\UI\Form\Subscription;

use Klapuch\Csrf;
use Klapuch\Form\EmptyStorage;
use Klapuch\Output;
use Klapuch\Snappie;
use Klapuch\Uri;
use Remembrall\Form\Subscription;
use Remembrall\Model\Subscribing;

require __DIR__ . '/../../../bootstrap.php';

final class DeleteForm extends \Tester\TestCase {
	use Snappie\Assertions;

	public function testOutput() {
		$this->assertXml(
			(new Subscription\DeleteForm(
				new Subscribing\FakeSubscription(
					null,
					new Output\Xml(['id' => 666], 'root')
				),
				new Uri\FakeUri(''),
				new Csrf\FakeProtection('pr073ct10n'),
				new EmptyStorage()
			))->render()
		);
	}
}

(new DeleteForm())->run();