<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Page\Participants;

use Klapuch\Application;
use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Misc;
use Remembrall\Page\Participants;
use Remembrall\Response;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class KickInteraction extends \Tester\TestCase {
	use TestCase\Page;

	public function testValidSubmitting() {
		$_POST['subscription'] = 1;
		$_POST['email'] = 'foo@email.cz';
		(new Misc\SamplePart($this->database))->try();
		(new Misc\SampleSubscription(
			$this->database,
			(new Misc\TestUsers($this->database))->register(),
			1
		))->try();
		(new Misc\SampleParticipant(
			$this->database,
			1,
			$_POST['email']
		))->try();
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri(''), 'subscriptions')
					),
					['success' => 'Participant has been kicked'],
					$_SESSION
				)
			),
			(new Participants\KickInteraction(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->template($_POST)
		);
	}
}

(new KickInteraction())->run();