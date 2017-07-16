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
use Remembrall\Response;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class UpPage extends \Tester\TestCase {
	use TestCase\Page;

	public function testValidContent() {
		Assert::noError(function() {
			$body = (new Sign\UpPage(
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
		$_POST['password'] = 'heslo123';
		$_POST['act'] = 'Register';
		$headers = (new Sign\UpPage(
			new Uri\FakeUri(''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->submitUp($_POST)->headers();
		Assert::same('/sign/in', $headers['Location']);
	}

	public function testErrorOnTakenEmail() {
		$_POST['email'] = 'me@me.cz';
		$_POST['password'] = 'heslo123';
		$_POST['act'] = 'Register';
		$this->database->exec(
			"INSERT INTO users (id, email, password, role) VALUES
			(2, '{$_POST['email']}', 'heslo', 'member')"
		);
		Assert::equal(
			new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl(new Uri\FakeUri(''), 'sign/up')
				),
				['danger' => 'Email "me@me.cz" already exists'],
				$_SESSION
			),
			(new Sign\UpPage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->submitUp($_POST)
		);
	}
}


(new UpPage())->run();