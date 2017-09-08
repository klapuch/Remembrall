<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\UI\Form\Subscription;

use Klapuch\Csrf;
use Klapuch\Output;
use Klapuch\Snappie;
use Klapuch\Uri;
use Remembrall\Form\Subscription;
use Remembrall\Model\Subscribing;

require __DIR__ . '/../../../bootstrap.php';

final class DeleteForms extends \Tester\TestCase {
	use Snappie\Assertions;

	public function testOutput() {
		$this->assertXml(
			sprintf(
				'<form>%s</form>',
				(new Subscription\DeleteForms(
					[
						new Subscribing\FakeSubscription(
							null,
							new Output\Xml(['id' => 666], 'root')
						),
						new Subscribing\FakeSubscription(
							null,
							new Output\Xml(['id' => 555], 'root')
						),
					],
					new Uri\FakeUri(''),
					new Csrf\FakeProtection('pr073ct10n')
				))->render()
			)
		);
	}
}

(new DeleteForms())->run();