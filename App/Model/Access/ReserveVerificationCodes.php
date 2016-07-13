<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Dibi;
use Remembrall\Exception;

/**
 * Reserve verification codes which can be given on demand in case the old one has been lost
 * With the "lost" is meant that the code was not received or occur other issue
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
		throw new Exception\NotFoundException(
			'For the given email, there is no valid verification code'
		);
	}
}