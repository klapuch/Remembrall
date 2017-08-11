<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Page\Participants;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Misc;
use Remembrall\Model\Subscribing;
use Remembrall\Page\Participants;
use Remembrall\Response;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class InviteInteraction extends \Tester\TestCase {
	use TestCase\Page;

	public function testValidSubmitting() {
		$_POST['subscription'] = 1;
		$_POST['email'] = 'foo@email.cz';
		$user = (new Misc\TestUsers($this->database))->register();
		$this->database->exec(
			"INSERT INTO subscriptions (id, user_id, part_id, interval, last_update, snapshot) VALUES
			(1, {$user->id()}, 4, 'PT3M', NOW(), '')"
		);
		$this->database->exec(
			"INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES
			(4, 'www.google.com', ROW('//google', 'xpath'), 'google content', 'google snap')"
		);
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri(''), 'subscriptions')
					),
					['success' => 'Participant has been asked'],
					$_SESSION
				)
			),
			(new Participants\InviteInteraction(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->template($_POST)
		);
	}

	public function testErrorOnParticipantAsMember() {
		$_POST['subscription'] = 1;
		$_POST['email'] = 'admin@admin.cz';
		(new Misc\TestUsers($this->database))->register($_POST['email']);
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri(''), 'subscriptions')
					),
					['danger' => 'Email "admin@admin.cz" is registered and can not be participant'],
					$_SESSION
				)
			),
			(new Participants\InviteInteraction(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->template($_POST)
		);
	}

	public function testErrorOnTooManyAttempts() {
		$_POST['subscription'] = 1;
		$_POST['email'] = 'foo@bar.cz';
		(new Misc\TestUsers($this->database))->register();
		$this->database->exec(
			"INSERT INTO subscriptions (id, user_id, part_id, interval, last_update, snapshot) VALUES
			(1, 0, 4, 'PT3M', NOW(), '')"
		);
		$participants = new Subscribing\NonViolentParticipants(
			new Access\FakeUser(),
			$this->database
		);
		$participants->invite($_POST['subscription'], $_POST['email']);
		$participants->invite($_POST['subscription'], $_POST['email']);
		$participants->invite($_POST['subscription'], $_POST['email']);
		$participants->invite($_POST['subscription'], $_POST['email']);
		$participants->invite($_POST['subscription'], $_POST['email']);
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri(''), 'subscriptions')
					),
					['danger' => '"foo@bar.cz" declined your invitation too many times'],
					$_SESSION
				)
			),
			(new Participants\InviteInteraction(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->template($_POST)
		);
	}
}

(new InviteInteraction())->run();