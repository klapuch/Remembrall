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

final class SecureVerificationCodes extends TestCase\Database {
	public function testGenerating() {
		(new Access\SecureVerificationCodes($this->database))
			->generate('fooBarEmail');
		Assert::same(
			91,
			$this->database->fetchSingle(
				'SELECT LENGTH(code)
				FROM verification_codes
				WHERE subscriber_id = 6'
			)
		);
	}

	protected function prepareDatabase() {
		$this->database->query('TRUNCATE verification_codes');
		$this->database->query('TRUNCATE subscribers');
		$this->database->query(
			'INSERT INTO subscribers (ID, email) VALUES (6, "fooBarEmail")'
		);
	}
}

(new SecureVerificationCodes())->run();
