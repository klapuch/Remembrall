<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Dibi;
use Klapuch\Encryption;

/**
 * Works just with secure reminders
 */
final class SecureForgottenPasswords implements ForgottenPasswords {
	private $database;
	private $cipher;

	public function __construct(
		Dibi\Connection $database,
		Encryption\Cipher $cipher
	) {
		$this->database = $database;
		$this->cipher = $cipher;
	}

	public function remind(string $email): RemindedPassword {
		$reminder = bin2hex(random_bytes(50)) . ':' . sha1($email);
		$this->database->query(
			'INSERT INTO forgotten_passwords (subscriber_id, reminder, reminded_at) VALUES
			((SELECT id FROM subscribers WHERE email IS NOT DISTINCT FROM ?), ?, ?)',
			$email,
			$reminder,
			new \DateTimeImmutable()
		);
		return new PostgresRemindedPassword(
			$reminder,
			$this->database,
			$this->cipher
		);
	}
}