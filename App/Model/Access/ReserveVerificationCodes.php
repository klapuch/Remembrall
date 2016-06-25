<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Dibi;
use Remembrall\Exception;

/**
 * Is able to GIVE verification code once more
 * Just in case the previous code has been lost
 */
final class ReserveVerificationCodes implements VerificationCodes {
	private $database;

	public function __construct(Dibi\Connection $database) {
		$this->database = $database;
	}

	public function generate(string $email): VerificationCode {
		$code = $this->database->fetchSingle(
			'SELECT code
			FROM verification_codes
			WHERE subscriber_id = (SELECT ID FROM subscribers WHERE email = ?)
			AND used = 0',
			$email
		);
		if($code)
			return new DisposableVerificationCode($code, $this->database);
		throw new Exception\ExistenceException(
			'For the given email, there is no valid verification code'
		);
	}
}