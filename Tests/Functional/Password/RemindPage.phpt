<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Password;

use Klapuch\Access;
use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Page\Password;
use Remembrall\TestCase;
use Remembrall\Response;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class RemindPage extends \Tester\TestCase {
	use TestCase\Page;

	public function testValidContent() {
		Assert::noError(function() {
			$body = (new Password\RemindPage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response([])->body()->serialization();
			$dom = new \DOMDocument();
			$dom->loadXML($body);
		});
	}

	public function testValidSubmitting() {
		$_POST['email'] = 'me@me.cz';
		$_POST['act'] = 'Remind';
		$this->database->exec(
			"INSERT INTO users (id, email, password, role) VALUES
            (1, '{$_POST['email']}', 'secret', 'member')"
		);
		$headers = (new Password\RemindPage(
			new Uri\FakeUri(''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->submitRemind($_POST)->headers();
		Assert::same('/sign/in', $headers['Location']);
	}

	public function testErrorOnTooManyAttempts() {
		$_POST['email'] = 'me@me.cz';
		$_POST['act'] = 'Remind';
		$this->database->exec(
			"INSERT INTO users (id, email, password, role) VALUES
            (1, '{$_POST['email']}', 'secret', 'member')"
		);
		$passwords = new Access\SecureForgottenPasswords($this->database);
		$passwords->remind($_POST['email']);
		$passwords->remind($_POST['email']);
		$passwords->remind($_POST['email']);
		Assert::equal(
			new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl(new Uri\FakeUri(''), 'password/remind')
				),
				['danger' => 'You have reached limit 3 forgotten passwords in last 24 hours'],
				$_SESSION
			),
			(new Password\RemindPage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->submitRemind($_POST)
		);
	}

	public function testErrorOnUnknownEmail() {
		$_POST['email'] = 'me@me.cz';
		$_POST['act'] = 'Remind';
		Assert::equal(
			new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl(new Uri\FakeUri(''), 'password/remind')
				),
				['danger' => 'The email does not exist'],
				$_SESSION
			),
			(new Password\RemindPage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->submitRemind($_POST)
		);
	}
}

(new RemindPage())->run();