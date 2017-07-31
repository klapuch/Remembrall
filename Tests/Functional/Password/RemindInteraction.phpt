<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Password;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Misc;
use Remembrall\Page\Password;
use Remembrall\Response;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class RemindInteraction extends \Tester\TestCase {
	use TestCase\Page;

	public function testValidSubmitting() {
		$_POST['email'] = 'me@me.cz';
		$_POST['act'] = 'Remind';
		(new Misc\TestUsers($this->database))->register($_POST['email']);
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri(''), 'sign/in')
					),
					['danger' => 'Password reset has been sent to your email'],
					$_SESSION
				)
			),
			(new Password\RemindInteraction(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response($_POST)
		);
	}

	public function testErrorOnTooManyAttempts() {
		$_POST['email'] = 'me@me.cz';
		$_POST['act'] = 'Remind';
		(new Misc\TestUsers($this->database))->register($_POST['email']);
		$passwords = new Access\SecureForgottenPasswords($this->database);
		$passwords->remind($_POST['email']);
		$passwords->remind($_POST['email']);
		$passwords->remind($_POST['email']);
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri(''), 'password/remind')
					),
					['danger' => 'You have reached limit 3 forgotten passwords in last 24 hours'],
					$_SESSION
				)
			),
			(new Password\RemindInteraction(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response($_POST)
		);
	}

	public function testErrorOnUnknownEmail() {
		$_POST['email'] = 'me@me.cz';
		$_POST['act'] = 'Remind';
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri(''), 'password/remind')
					),
					['danger' => 'The email does not exist'],
					$_SESSION
				)
			),
			(new Password\RemindInteraction(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response($_POST)
		);
	}
}

(new RemindInteraction())->run();