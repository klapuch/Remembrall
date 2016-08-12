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
			$this->database->fetchColumn(
				'SELECT LENGTH(code)
				FROM verification_codes
				WHERE subscriber_id = 6'
			)
		);
	}

	protected function prepareDatabase() {
		$this->purge(['verification_codes', 'subscribers']);
		$this->database->query(
			"INSERT INTO subscribers (id, email, password) VALUES
			(6, 'fooBarEmail', 'password')"
		);
	}
}

(new SecureVerificationCodes())->run();
