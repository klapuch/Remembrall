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

final class RetryForm extends \Tester\TestCase {
	use Snappie\Assertions;

	public function testOutputWithSubmitType() {
		$this->assertXml(
			(new Participants\RetryForm(
				new Subscribing\FakeParticipant(
					new Output\Xml(
						[
							'id' => 666,
							'subscription_id' => 555,
							'email' => 'me@participant.cz',
						],
						'root'
					)
				),
				new Uri\FakeUri(''),
				new Csrf\FakeProtection('pr073ct10n')
			))->render()
		);
	}

	public function testOutputWithButton() {
		$this->assertXml(
			(new Participants\RetryForm(
				new Subscribing\FakeParticipant(
					new Output\Xml(
						[
							'id' => 666,
							'subscription_id' => 555,
							'email' => 'me@participant.cz',
							'harassed' => 'true',
						],
						'root'
					)
				),
				new Uri\FakeUri(''),
				new Csrf\FakeProtection('pr073ct10n')
			))->render()
		);
	}
}

(new RetryForm())->run();