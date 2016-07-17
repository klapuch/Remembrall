<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Dibi;
use Remembrall\Exception;

/**
 * Verification code which can be used just once
 */
final class DisposableVerificationCode implements VerificationCode {
	private $code;
	private $database;

	public function __construct(string $code, Dibi\Connection $database) {
		$this->code = $code;
		$this->database = $database;
	}

	public function use() {
		if($this->used()) {
			throw new Exception\DuplicateException(
				'Verification code was already used'
			);
		}
		$this->database->query(
			'UPDATE verification_codes
			SET used = 1, used_at = NOW()
			WHERE code = ?',
			$this->code
		);
	}

	public function owner(): Subscriber {
		return new MySqlSubscriber(
			(int)$this->database->fetchSingle(
				'SELECT subscriber_id
				FROM verification_codes
				WHERE code = ?',
				$this->code
			),
			$this->database
		);
	}

	/**
	 * Was the verification code already used?
	 * @return bool
	 */
	private function used(): bool {
		return (bool)$this->database->fetchSingle(
			'SELECT 1
			FROM verification_codes
			WHERE code = ?
			AND used = 1',
			$this->code
		);
	}
}