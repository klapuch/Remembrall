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

final class DisposableVerificationCode extends TestCase\Database {
	public function testSuccessfulUsing() {
		$this->prepareValidCode();
		(new Access\DisposableVerificationCode(
			'valid:code',
			$this->database
		))->use();
		Assert::same(
			1,
			$this->database->fetchSingle(
				'SELECT used FROM verification_codes WHERE code = "valid:code"'
			)
		);
	}

	/**
	 * @throws \Remembrall\Exception\DuplicateException Verification code was already used
	 */
	public function testAlreadyActivatedCode() {
		$this->database->query(
			'INSERT INTO verification_codes (subscriber_id, code, used)
			VALUES (2, "activated:code", 1)'
		);
		(new Access\DisposableVerificationCode(
			'activated:code',
			$this->database
		))->use();
	}

	public function testOwner() {
		$this->prepareValidCode();
		$identity = (new Access\DisposableVerificationCode(
			'valid:code',
			$this->database
		))->owner();
		Assert::same(1, $identity->id());
		Assert::same('foo@gmail.com', $identity->email());
	}

	private function prepareValidCode() {
		$this->database->query('TRUNCATE subscribers');
		$this->database->query(
			'INSERT INTO verification_codes (subscriber_id, code, used)
			VALUES (1, "valid:code", 0)'
		);
		$this->database->query(
			'INSERT INTO subscribers (ID, email) VALUES (1, "foo@gmail.com")'
		);
	}

	protected function prepareDatabase() {
		$this->database->query('TRUNCATE verification_codes');
	}
}

(new DisposableVerificationCode())->run();
