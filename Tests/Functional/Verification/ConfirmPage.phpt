<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Verification;

use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Page\Verification;
use Remembrall\Response;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ConfirmPage extends \Tester\TestCase {
	use TestCase\Page;

	public function testErrorOnUnknownCode() {
		Assert::equal(
			new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl(new Uri\FakeUri(''), 'sign/in')
				),
				['danger' => 'The verification code does not exist'],
				$_SESSION
			),
			(new Verification\ConfirmPage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response(['code' => 'abc123'])
		);
	}

	public function testErrorOnUsedToken() {
		$code = 'abc123';
		$this->database->exec(
			"INSERT INTO verification_codes (user_id, code, used) VALUES
            (2, '$code', TRUE)"
		);
		Assert::equal(
			new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl(new Uri\FakeUri(''), 'sign/in')
				),
				['danger' => 'Verification code was already used'],
				$_SESSION
			),
			(new Verification\ConfirmPage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response(['code' => $code])
		);
	}

	public function testSigningInOnValidCode() {
		$code = 'valid:code';
		$this->database->exec(
			"INSERT INTO verification_codes (user_id, code, used) VALUES
            (2, '$code', FALSE)"
		);
		$this->database->exec(
			"INSERT INTO users (id, email, password, role) VALUES
            (2, 'me@boss.cz', 'secret', 'member')"
		);
		$headers = (new Verification\ConfirmPage(
			new Uri\FakeUri(''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->response(['code' => $code])->headers();
		Assert::same(['Location' => '/subscriptions'], $headers);
		Assert::same(2, $_SESSION['id']);
	}
}

(new ConfirmPage())->run();