<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\UI\Form\Participants;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Output;
use Klapuch\Snappie;
use Klapuch\Uri;
use Remembrall\Form\Participants;
use Remembrall\Model\Subscribing;

require __DIR__ . '/../../../bootstrap.php';

final class InviteForms extends \Tester\TestCase {
	use Snappie\Assertions;

	public function testOutput() {
		$this->assertXml(
			sprintf(
				'<forms>%s</forms>',
				(new Participants\InviteForms(
					[
						new Subscribing\FakeSubscription(
							null,
							new Output\Xml(['id' => 555], 'root')
						),
						new Subscribing\FakeSubscription(
							null,
							new Output\Xml(['id' => 666], 'root')
						),
					],
					new Uri\FakeUri(''),
					new Csrf\FakeProtection('pr073ct10n'),
					new Form\EmptyStorage()
				))->render()
			)
		);
	}
}

(new InviteForms())->run();