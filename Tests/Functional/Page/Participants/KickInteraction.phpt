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
		$user = (new Misc\TestUsers($this->database))->register();
		$this->database->exec(
			"INSERT INTO subscriptions (id, user_id, part_id, interval, last_update, snapshot) VALUES
			(1, {$user->id()}, 4, 'PT3M', NOW(), '')"
		);
		$this->database->exec(
			"INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES
			(4, 'www.google.com', ROW('//google', 'xpath'), 'google content', 'google snap')"
		);
		$this->database->exec(
			"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) 
			VALUES ('{$_POST['email']}', 1, 'abc', NOW(), FALSE, NULL)"
		);
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
			))->response($_POST)
		);
	}
}

(new KickInteraction())->run();