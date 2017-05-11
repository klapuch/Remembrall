<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Sign;

use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Page\Sign;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class InPage extends TestCase\Page {
	public function testValidContent() {
		Assert::noError(function() {
			$body = (new Sign\InPage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response([])->body()->serialization();
			$dom = new \DOMDocument();
			$dom->loadXML($body);
		});
	}

	public function testErrorOnEnteringWithWrongCredentials() {
		$headers = (new Sign\InPage(
			new Uri\FakeUri(''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->submitIn(['email' => 'me@boss.cz', 'password' => 'secret'])->headers();
		Assert::same(['Location' => '/sign/in'], $headers);
	}

	public function testLoggingWithCorrectCredentials() {
		$_POST['email'] = 'klapuchdominik@gmail.com';
		$_POST['password'] = 'heslo';
		$_POST['act'] = 'Login';
		$this->purge(['users', 'verification_codes']);
		$this->database->exec(
			"INSERT INTO users (id, email, password, role) VALUES
			(2, 'klapuchdominik@gmail.com', 'dc98d5af8f15840afcab387d5923f330df4a7bc76625e024fec2cb1f626543dccf352999ffd4e3c15047bee301104d06651ccaaee60ed3b98723b1e04cbaa429e00f088976bd9b5a94d5863f1d124ee8', 'member')"
		);
		$this->database->exec(
			"INSERT INTO verification_codes (user_id, code, used, used_at) VALUES
			(2, 'c7fb39e3b3e0d9efa6fce134b703fcea5c4c4196cef0dcaccf:3b59944087428cd5b95be4f18dcf06b8815b9fa6', TRUE, NOW());"
		);
		$headers = (new Sign\InPage(
			new Uri\FakeUri(''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->submitIn(['email' => $_POST['email'], 'password' => $_POST['password']])->headers();
		Assert::same(['Location' => '/subscriptions'], $headers);
		Assert::same(2, $_SESSION['id']);
	}
}

(new InPage())->run();