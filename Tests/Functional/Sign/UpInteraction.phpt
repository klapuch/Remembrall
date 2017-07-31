<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Sign;

use Klapuch\Application;
use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Misc;
use Remembrall\Page\Sign;
use Remembrall\Response;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class UpInteraction extends \Tester\TestCase {
	use TestCase\Page;

	public function testSigningUpWithoutError() {
		$_POST['email'] = 'me@me.cz';
		$_POST['password'] = 'heslo123';
		$_POST['act'] = 'Register';
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\InformativeResponse(
						new Response\RedirectResponse(
							new Response\EmptyResponse(),
							new Uri\RelativeUrl(new Uri\FakeUri(''), 'sign/in')
						),
						['warning' => 'Confirm your registration in the email'],
						$_SESSION
					),
					['success' => 'You have been signed up'],
					$_SESSION
				)
			),
			(new Sign\UpInteraction(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response($_POST)
		);
	}

	public function testErrorOnTakenEmail() {
		$_POST['email'] = 'me@me.cz';
		$_POST['password'] = 'heslo123';
		$_POST['act'] = 'Register';
		(new Misc\TestUsers($this->database))->register($_POST['email']);
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri(''), 'sign/up')
					),
					['danger' => 'Email "me@me.cz" already exists'],
					$_SESSION
				)
			),
			(new Sign\UpInteraction(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response($_POST)
		);
	}
}


(new UpInteraction())->run();