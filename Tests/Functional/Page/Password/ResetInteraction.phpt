<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Page\Password;

use Klapuch\Application;
use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Page\Password;
use Remembrall\Response;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class ResetInteraction extends \Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResetting() {
		$_POST['password'] = 'heslo';
		$_POST['reminder'] = '123abc123';
		$_POST['act'] = 'Send';
		$statement = $this->database->prepare(
			"INSERT INTO forgotten_passwords (user_id, used, reminder, reminded_at, expire_at) VALUES
			(1, FALSE, ?, NOW(), NOW() + INTERVAL '10 MINUTE')"
		);
		$statement->execute([$_POST['reminder']]);
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri('', ''), 'sign/in')
					),
					['success' => 'Password has been reset'],
					$_SESSION
				)
			),
			(new Password\ResetInteraction(
				new Uri\FakeUri('', ''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->template($_POST)
		);
	}

	public function testErrorOnUnknownReminder() {
		$_POST['password'] = 'heslo';
		$_POST['reminder'] = '123abc123';
		$_POST['act'] = 'Send';
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri('', ''), 'password/remind')
					),
					['danger' => 'The reminder does not exist'],
					$_SESSION
				)
			),
			(new Password\ResetInteraction(
				new Uri\FakeUri('', ''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->template($_POST)
		);
	}
}

(new ResetInteraction())->run();