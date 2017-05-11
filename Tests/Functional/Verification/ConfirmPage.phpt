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

final class ConfirmPage extends TestCase\Page {
	public function testRedirectToLoginOnUnknownCode() {
		$headers = (new Verification\ConfirmPage(
			new Uri\FakeUri(''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->response(['code' => 'abc123'])->headers();
		Assert::same(['Location' => '/sign/in'], $headers);
	}

	public function testLoggingOnValidCode() {
		$this->purge(['users', 'verification_codes']);
		$code = 'valid:code';
		$statement = $this->database->prepare(
			'INSERT INTO verification_codes (user_id, code, used) VALUES
            (2, ?, FALSE)'
		);
		$statement->execute([$code]);
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