<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Klapuch\Storage;

/**
 * Collection of forgotten passwords which can be reminded just X times in Y hours
 */
final class LimitedForgottenPasswords implements ForgottenPasswords {
	const ATTEMPT_LIMIT = 3;
	const HOUR_LIMIT = 24;
	// 3 attempts in last 24 hours
	private $origin;
	private $database;

	public function __construct(
		ForgottenPasswords $origin,
		Storage\Database $database
	) {
		$this->origin = $origin;
		$this->database = $database;
	}

	public function remind(string $email): RemindedPassword {
		if($this->overstepped($email)) {
			throw new \OverflowException(
				sprintf(
					'You have reached limit %d forgotten passwords in last %d hours',
					self::ATTEMPT_LIMIT,
					self::HOUR_LIMIT
				)
			);
		}
		return $this->origin->remind($email);
	}

	/**
	 * Is the limit overstepped?
	 * @param string $email
	 * @return bool
	 */
	private function overstepped(string $email): bool {
		return (bool)$this->database->fetchColumn(
			"SELECT 1
			FROM forgotten_passwords
			WHERE subscriber_id = (
				SELECT id
				FROM subscribers
				WHERE email IS NOT DISTINCT FROM ?
			)
			AND reminded_at > NOW() - INTERVAL '1 HOUR' * ?
			HAVING COUNT(id) >= ?",
			[$email, self::HOUR_LIMIT, self::ATTEMPT_LIMIT]
		);
	}
}