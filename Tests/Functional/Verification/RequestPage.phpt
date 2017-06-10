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

	public function testValidSubmitting() {
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

	public function testErrorSubmittingRedirectingToSamePage() {
		$headers = (new Verification\RequestPage(
			new Uri\FakeUri(''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->submitRequest([])->headers();
		Assert::same('/verification/request', $headers['Location']);
	}
}

(new RequestPage())->run();