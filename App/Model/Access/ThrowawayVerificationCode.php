<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Klapuch\Storage;
use Remembrall\Exception;

/**
 * Verification code which can be used just once
 */
final class ThrowawayVerificationCode implements VerificationCode {
	private $code;
	private $database;

	public function __construct(string $code, Storage\Database $database) {
		$this->code = $code;
		$this->database = $database;
	}

	public function use() {
		if($this->used()) {
			throw new Exception\NotFoundException(
				'Verification code was already used'
			);
		}
		$this->database->query(
			'UPDATE verification_codes
			SET used = TRUE, used_at = NOW()
			WHERE code IS NOT DISTINCT FROM ?',
			[$this->code]
		);
	}

	public function owner(): Subscriber {
		return new PostgresSubscriber(
			(int)$this->database->fetchColumn(
				'SELECT subscriber_id
				FROM verification_codes
				WHERE code IS NOT DISTINCT FROM ?',
				[$this->code]
			),
			$this->database
		);
	}

	/**
	 * Was the verification code already used?
	 * @return bool
	 */
	private function used(): bool {
		return (bool)$this->database->fetchColumn(
			'SELECT 1
			FROM verification_codes
			WHERE code IS NOT DISTINCT FROM ?
			AND used = TRUE',
			[$this->code]
		);
	}
}
