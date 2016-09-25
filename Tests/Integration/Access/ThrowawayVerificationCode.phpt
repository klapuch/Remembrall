<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Access;

use Remembrall\Model\Access;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ThrowawayVerificationCode extends TestCase\Database {
	public function testSuccessfulUsing() {
		$this->prepareValidCode();
		(new Access\ThrowawayVerificationCode(
			'valid:code',
			$this->database
		))->use();
		Assert::true(
			$this->database->fetchColumn(
				"SELECT used
				FROM verification_codes
				WHERE code = 'valid:code'"
			)
		);
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException Verification code was already used
	 */
	public function testUsingAlreadyActivatedCode() {
		$this->database->query(
			"INSERT INTO verification_codes (subscriber_id, code, used, used_at) VALUES
			(2, 'activated:code', TRUE, NOW())"
		);
		(new Access\ThrowawayVerificationCode(
			'activated:code',
			$this->database
		))->use();
	}

	public function testOwner() {
		$this->prepareValidCode();
		$identity = (new Access\ThrowawayVerificationCode(
			'valid:code',
			$this->database
		))->owner();
		Assert::same(1, $identity->id());
		Assert::same('foo@gmail.com', $identity->email());
	}

	private function prepareValidCode() {
		$this->database->query(
			"INSERT INTO subscribers (id, email, password) VALUES
			(1, 'foo@gmail.com', 'password')"
		);
		$this->database->query(
			"INSERT INTO verification_codes (subscriber_id, code, used)
			VALUES (1, 'valid:code', FALSE)"
		);
	}

	protected function prepareDatabase() {
		$this->purge(['verification_codes', 'subscribers']);
	}
}

(new ThrowawayVerificationCode())->run();
