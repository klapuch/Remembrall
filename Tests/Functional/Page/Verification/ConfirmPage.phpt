<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Page\Verification;

use Klapuch\Application;
use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Misc;
use Remembrall\Page\Verification;
use Remembrall\Response;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class ConfirmPage extends \Tester\TestCase {
	use TestCase\Page;

	public function testErrorOnUnknownCode() {
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri(''), 'sign/in')
					),
					['danger' => 'The verification code does not exist'],
					$_SESSION
				)
			),
			(new Verification\ConfirmPage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->template(['code' => 'abc123'])
		);
	}

	public function testErrorOnUsedToken() {
		$code = 'abc123';
		(new Misc\SampleVerificationCode($this->database, ['code' => $code, 'used' => true]))->try();
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri(''), 'sign/in')
					),
					['danger' => 'Verification code was already used'],
					$_SESSION
				)
			),
			(new Verification\ConfirmPage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->template(['code' => $code])
		);
	}

	public function testSigningInOnValidCode() {
		$code = 'valid:code';
		(new Misc\SampleVerificationCode($this->database, ['code' => $code, 'used' => false, 'user' => 2]))->try();
		(new Misc\SampleUser($this->database))->try();
		(new Misc\SampleUser($this->database))->try();
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\InformativeResponse(
						new Response\RedirectResponse(
							new Response\EmptyResponse(),
							new Uri\RelativeUrl(new Uri\FakeUri(''), 'subscriptions')
						),
						['success' => 'Your code has been confirmed'],
						$_SESSION
					),
					['success' => 'You have been logged in'],
					$_SESSION
				)
			),
			(new Verification\ConfirmPage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->template(['code' => $code])
		);
		Assert::same('2', $_SESSION['id']);
	}
}

(new ConfirmPage())->run();