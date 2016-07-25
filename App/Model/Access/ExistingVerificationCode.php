<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Dibi;
use Remembrall\Exception;

/**
 * Existing verification code
 */
final class ExistingVerificationCode implements VerificationCode {
	private $origin;
	private $code;
	private $database;

	public function __construct(
		VerificationCode $origin,
		string $code,
		Dibi\Connection $database
	) {
		$this->origin = $origin;
		$this->code = $code;
		$this->database = $database;
	}

	public function use () {
		if(!$this->exists()) {
			throw new Exception\NotFoundException(
				'The verification code does not exist'
			);
		}
		$this->origin->use();
	}

	public function owner(): Subscriber {
		if(!$this->exists()) {
			throw new Exception\NotFoundException(
				'Nobody owns the verification code'
			);
		}
		return $this->origin->owner();
	}

	/**
	 * Does the verification code exist?
	 * @return bool
	 */
	private function exists(): bool {
		return (bool)$this->database->fetchSingle(
			'SELECT 1
			FROM verification_codes
			WHERE code = ?',
			$this->code
		);
	}
}