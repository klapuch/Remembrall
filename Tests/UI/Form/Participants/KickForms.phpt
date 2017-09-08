<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\UI\Form\Participants;

use Klapuch\Csrf;
use Klapuch\Output;
use Klapuch\Snappie;
use Klapuch\Uri;
use Remembrall\Form\Participants;
use Remembrall\Model\Subscribing;

require __DIR__ . '/../../../bootstrap.php';

final class KickForms extends \Tester\TestCase {
	use Snappie\Assertions;

	public function testOutput() {
		$this->assertXml(
			sprintf(
				'<forms>%s</forms>',
				(new Participants\KickForms(
					[
						new Subscribing\FakeParticipant(
							new Output\Xml(
								[
									'id' => 666,
									'subscription_id' => 666,
									'email' => 'me@participant.cz',
								],
								'root'
							)
						),
						new Subscribing\FakeParticipant(
							new Output\Xml(
								[
									'id' => 777,
									'subscription_id' => 888,
									'email' => 'you@participant.cz',
								],
								'root'
							)
						),
					],
					new Uri\FakeUri(''),
					new Csrf\FakeProtection('pr073ct10n')
				))->render()
			)
		);
	}
}

(new KickForms())->run();