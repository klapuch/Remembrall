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

final class ReserveVerificationCodes extends TestCase\Database {
	public function testRegenerating() {
		$this->database->query(
			'INSERT INTO verification_codes (subscriber_id, code, used)
			VALUES (6, "123456", 0)'
		);
		$code = (new Access\ReserveVerificationCodes($this->database))
			->generate('foo@bar.cz');
		Assert::equal(
			new Access\DisposableVerificationCode('123456', $this->database),
			$code
		);
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException For the given email, there is no valid verification code
	 */
	public function testRegeneratingForUsedOne() {
		$this->database->query(
			'INSERT INTO verification_codes (subscriber_id, code, used)
			VALUES (6, "123456", 1)'
		);
		(new Access\ReserveVerificationCodes($this->database))
			->generate('foo@bar.cz');
	}

	protected function prepareDatabase() {
		$this->database->query('TRUNCATE subscribers');
		$this->database->query('TRUNCATE verification_codes');
		$this->database->query(
			'INSERT INTO subscribers (ID, email) VALUES (6, "foo@bar.cz")'
		);
	}
}

(new ReserveVerificationCodes())->run();
