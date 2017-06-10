<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Password;

use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Page\Password;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ResetPage extends \Tester\TestCase {
	use TestCase\Page;

	public function testRedirectForInvalidReminder() {
		$headers = (new Password\ResetPage(
			new Uri\FakeUri(''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->response(['reminder' => 'abc123'])->headers();
		Assert::same(['Location' => '/password/remind'], $headers);
	}

	public function testWorkingResponseForValidReminder() {
		$reminder = '123abc123';
		$statement = $this->database->prepare(
			"INSERT INTO forgotten_passwords (user_id, used, reminder, reminded_at, expire_at) VALUES
            (1, FALSE, ?, NOW(), NOW() + INTERVAL '10 MINUTE')"
		);
		$statement->execute([$reminder]);
		Assert::noError(function() use ($reminder) {
			$body = (new Password\ResetPage(
				new Uri\FakeUri('', ''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response(['reminder' => $reminder])->body()->serialization();
			$dom = new \DOMDocument();
			$dom->loadXML($body);
		});
	}

	public function testValidSubmitting() {
		$_POST['password'] = 'heslo';
		$_POST['reminder'] = '123abc123';
		$_POST['act'] = 'Send';
		$statement = $this->database->prepare(
			"INSERT INTO forgotten_passwords (user_id, used, reminder, reminded_at, expire_at) VALUES
			(1, FALSE, ?, NOW(), NOW() + INTERVAL '10 MINUTE')"
		);
		$statement->execute([$_POST['reminder']]);
		$headers = (new Password\ResetPage(
			new Uri\FakeUri('', ''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->submitReset($_POST)->headers();
		Assert::same(['Location' => '/sign/in'], $headers);
	}

	public function testErrorSubmit() {
		$_POST['password'] = 'heslo';
		$_POST['reminder'] = '123abc123';
		$_POST['act'] = 'Send';
		$headers = (new Password\ResetPage(
			new Uri\FakeUri('', ''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->submitReset($_POST)->headers();
		Assert::same(['Location' => '/password/remind'], $headers);
	}
}

(new ResetPage())->run();