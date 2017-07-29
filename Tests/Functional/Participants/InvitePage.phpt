<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Participants;

use Klapuch\Access;
use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Model\Subscribing;
use Remembrall\Page\Participants;
use Remembrall\Response;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class InvitePage extends \Tester\TestCase {
	use TestCase\Page;

	public function testBlockingGet() {
		$headers = (new Participants\InvitePage(
			new Uri\FakeUri(''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->response([])->headers();
		Assert::same(['Location' => '/error'], $headers);
	}

	public function testValidSubmitting() {
		$_POST['subscription'] = 1;
		$_POST['email'] = 'foo@email.cz';
		$this->database->exec(
			"INSERT INTO subscriptions (id, user_id, part_id, interval, last_update, snapshot) VALUES
			(1, 0, 4, 'PT3M', NOW(), '')"
		);
		$this->database->exec(
			"INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES
			(4, 'www.google.com', ROW('//google', 'xpath'), 'google content', 'google snap')"
		);
		$this->database->exec(
			"INSERT INTO users (id, email, password, role) VALUES
            (0, 'admin@admin.cz', 'secret', 'member')"
		);
		Assert::equal(
			new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl(new Uri\FakeUri(''), 'subscriptions')
				),
				['success' => 'Participant has been asked'],
				$_SESSION
			),
			(new Participants\InvitePage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->post($_POST)
		);
	}

	public function testErrorOnParticipantAsMember() {
		$_POST['subscription'] = 1;
		$_POST['email'] = 'admin@admin.cz';
		$this->database->exec(
			"INSERT INTO users (id, email, password, role) VALUES
            (0, 'admin@admin.cz', 'secret', 'member')"
		);
		Assert::equal(
			new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl(new Uri\FakeUri(''), 'subscriptions')
				),
				['danger' => 'Email "admin@admin.cz" is registered and can not be participant'],
				$_SESSION
			),
			(new Participants\InvitePage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->post($_POST)
		);
	}

	public function testErrorOnTooManyAttempts() {
		$_POST['subscription'] = 1;
		$_POST['email'] = 'foo@bar.cz';
		$this->database->exec(
			"INSERT INTO subscriptions (id, user_id, part_id, interval, last_update, snapshot) VALUES
			(1, 0, 4, 'PT3M', NOW(), '')"
		);
		$this->database->exec(
			"INSERT INTO users (id, email, password, role) VALUES
            (0, 'admin@admin.cz', 'secret', 'member')"
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
			new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl(new Uri\FakeUri(''), 'subscriptions')
				),
				['danger' => '"foo@bar.cz" declined your invitation too many times'],
				$_SESSION
			),
			(new Participants\InvitePage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->post($_POST)
		);
	}
}

(new InvitePage())->run();