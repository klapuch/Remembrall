<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Klapuch\Storage;

/**
 * Works just with securely generated codes
 */
final class SecureVerificationCodes implements VerificationCodes {
	private $database;

	public function __construct(Storage\Database $database) {
		$this->database = $database;
	}

	public function generate(string $email): VerificationCode {
		$code = bin2hex(random_bytes(25)) . ':' . sha1($email);
		$this->database->query(
			'INSERT INTO verification_codes (subscriber_id, code)
			VALUES ((SELECT id FROM subscribers WHERE email IS NOT DISTINCT FROM ?), ?)',
			[$email, $code]
		);
		return new DisposableVerificationCode($code, $this->database);
	}
}