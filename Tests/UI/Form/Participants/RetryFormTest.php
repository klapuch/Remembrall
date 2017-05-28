<?php
declare(strict_types = 1);
namespace Remembrall\UI\Form\Participants;

use Klapuch\Csrf;
use Klapuch\Output;
use Klapuch\Uri;
use Remembrall\Form\Participants;
use Remembrall\Model\Subscribing;
use Spatie\Snapshots;

final class RetryFormTest extends \PHPUnit\Framework\TestCase {
	use Snapshots\MatchesSnapshots;

	public function testOutputWithSubmitType() {
		$this->assertMatchesXmlSnapshot(
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
		$this->assertMatchesXmlSnapshot(
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