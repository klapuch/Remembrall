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

final class RequestPage extends \Tester\TestCase {
	use TestCase\Page;

	public function testWorkingResponse() {
		Assert::noError(function() {
			$body = (new Verification\RequestPage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response([])->body()->serialization();
			$dom = new \DOMDocument();
			$dom->loadXML($body);
		});
	}

	public function testValidRequesting() {
		$_POST['email'] = 'me@me.cz';
		$_POST['act'] = 'Request';
		$user = 2;
		$this->database->exec(
			"INSERT INTO users (id, email, password, role) VALUES
            ($user, '{$_POST['email']}', 'secret', 'member')"
		);
		$this->database->exec(
			"INSERT INTO verification_codes (user_id, code, used) VALUES
            ($user, 'valid:code', FALSE)"
		);
		$headers = (new Verification\RequestPage(
			new Uri\FakeUri(''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->submitRequest($_POST)->headers();
		Assert::same('/sign/in', $headers['Location']);
	}

	public function testErrorOnUnknownEmail() {
		$_POST['email'] = 'me@me.cz';
		$_POST['act'] = 'Request';
		Assert::equal(
			new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl(new Uri\FakeUri(''), 'verification/request')
				),
				['danger' => 'For the given email, there is no valid verification code'],
				$_SESSION
			),
			(new Verification\RequestPage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->submitRequest($_POST)
		);
	}
}

(new RequestPage())->run();