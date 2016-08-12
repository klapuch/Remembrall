<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Klapuch\Storage;
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
		Storage\Database $database
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
		return (bool)$this->database->fetchColumn(
			'SELECT 1
			FROM verification_codes
			WHERE code IS NOT DISTINCT FROM ?',
			[$this->code]
		);
	}
}