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

final class KickForm extends \Tester\TestCase {
	use Snappie\Assertions;

	public function testOutput() {
		$this->assertXml(
			(new Participants\KickForm(
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
				new Uri\FakeUri(''),
				new Csrf\FakeProtection('pr073ct10n'),
				new Form\EmptyStorage()
			))->render()
		);
	}
}

(new KickForm())->run();