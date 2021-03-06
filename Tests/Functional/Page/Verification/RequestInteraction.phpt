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

final class RequestInteraction extends \Tester\TestCase {
	use TestCase\Page;

	public function testValidRequesting() {
		$_POST['email'] = 'me@me.cz';
		$_POST['act'] = 'Request';
		(new Misc\SampleUser($this->database))->try();
		(new Misc\SampleUser($this->database, $_POST))->try();
		(new Misc\SampleVerificationCode($this->database, ['used' => false, 'user' => 2]))->try();
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri('', '/verification/request'), 'sign/in')
					),
					['success' => 'Verification code has been resent'],
					$_SESSION
				)
			),
			(new Verification\RequestInteraction(
				new Uri\FakeUri('', '/verification/request'),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->template($_POST)
		);
	}

	public function testErrorOnUnknownEmail() {
		$_POST['email'] = 'me@me.cz';
		$_POST['act'] = 'Request';
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri(''), 'verification/request')
					),
					['danger' => 'For the given email, there is no valid verification code'],
					$_SESSION
				)
			),
			(new Verification\RequestInteraction(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->template($_POST)
		);
	}
}

(new RequestInteraction())->run();