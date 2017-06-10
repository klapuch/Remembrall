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

final class RemindPage extends \Tester\TestCase {
	use TestCase\Page;

	public function testValidContent() {
		Assert::noError(function() {
			$body = (new Password\RemindPage(
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
		$_POST['act'] = 'Remind';
		$this->purge(['users', 'forgotten_passwords']);
		$this->database->exec(
			"INSERT INTO users (id, email, password, role) VALUES
            (1, '{$_POST['email']}', 'secret', 'member')"
		);
		$this->database->exec(
			"INSERT INTO forgotten_passwords (user_id, used, reminder, reminded_at, expire_at) VALUES
            (1, FALSE, 'abc', NOW(), NOW() + INTERVAL '10 MINUTE')"
		);
		$headers = (new Password\RemindPage(
			new Uri\FakeUri(''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->submitRemind($_POST)->headers();
		Assert::same('/sign/in', $headers['Location']);
	}

	public function testErrorSubmittingRedirectingToSamePage() {
		$response = (new Password\RemindPage(
			new Uri\FakeUri(''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->submitRemind([]);
		Assert::same('/password/remind', $response->headers()['Location']);
	}
}

(new RemindPage())->run();