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

final class InInteraction extends \Tester\TestCase {
	use TestCase\Page;

	public function testErrorOnEnteringWithoutActivation() {
		$_POST['email'] = 'klapuchdominik@gmail.com';
		$_POST['password'] = 'heslo';
		$_POST['act'] = 'Login';
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri(''), 'sign/in')
					),
					['danger' => 'Email has not been verified yet'],
					$_SESSION
				)
			),
			(new Sign\InInteraction(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response(['email' => 'me@boss.cz', 'password' => 'secret'])
		);
	}

	public function testErrorOnEnteringWithWrongCredentials() {
		$_POST['email'] = 'klapuchdominik@gmail.com';
		$_POST['password'] = 'heslo';
		$_POST['act'] = 'Login';
		(new Misc\TestUsers($this->database))->register($_POST['email']);
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri(''), 'sign/in')
					),
					['danger' => 'Wrong password'],
					$_SESSION
				)
			),
			(new Sign\InInteraction(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response(['email' => 'klapuchdominik@gmail.com', 'password' => 'secret'])
		);
	}

	public function testValidSubmitting() {
		$_POST['email'] = 'klapuchdominik@gmail.com';
		$_POST['password'] = 'heslo';
		$_POST['act'] = 'Login';
		(new Misc\TestUsers($this->database))->register($_POST['email']);
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri(''), 'subscriptions')
					),
					['success' => 'You have been logged in'],
					$_SESSION
				)
			),
			(new Sign\InInteraction(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response(['email' => $_POST['email'], 'password' => $_POST['password']])
		);
		Assert::same(1, $_SESSION['id']);
	}
}

(new InInteraction())->run();