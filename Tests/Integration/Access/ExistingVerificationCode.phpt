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

final class ExistingVerificationCode extends TestCase\Database {
	/**
	 * @throws \Remembrall\Exception\NotFoundException The verification code does not exist
	 */
	public function testUsingUnknownCode() {
		(new Access\ExistingVerificationCode(
			new Access\FakeVerificationCode(),
			'123',
			$this->database
		))->use();
	}

	public function testUsingKnownCode() {
		$this->prepareCode();
		Assert::noError(
			function() {
				(new Access\ExistingVerificationCode(
					new Access\FakeVerificationCode(),
					'123',
					$this->database
				))->use();
			}
		);
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException Nobody owns the verification code
	 */
	public function testCodeBelongingToNobody() {
		(new Access\ExistingVerificationCode(
			new Access\FakeVerificationCode(),
			'123',
			$this->database
		))->owner();
	}

	public function testOwnedCode() {
		$this->prepareCode();
		$owner = new Access\FakeSubscriber();
		Assert::same(
			$owner,
			(new Access\ExistingVerificationCode(
				new Access\FakeVerificationCode($owner),
				'123',
				$this->database
			))->owner()
		);
	}

	private function prepareCode() {
		$this->database->query(
			'INSERT INTO verification_codes (subscriber_id, code, used) VALUES
			(2, "123", FALSE)'
		);
	}

	protected function prepareDatabase() {
		$this->purge(['verification_codes']);
	}
}

(new ExistingVerificationCode())->run();
