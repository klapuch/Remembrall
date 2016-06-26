<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Dibi;
use Remembrall\Model\Security;

/**
 * Works just with secure reminders
 */
final class SecureForgottenPasswords implements ForgottenPasswords {
	private $database;
	private $cipher;

	public function __construct(
		Dibi\Connection $database,
		Security\Cipher $cipher
	) {
		$this->database = $database;
		$this->cipher = $cipher;
	}

	public function remind(string $email): RemindedPassword {
		$reminder = bin2hex(random_bytes(50)) . ':' . sha1($email);
		$this->database->query(
			'INSERT INTO forgotten_passwords (subscriber_id, reminder)
			VALUES ((SELECT ID FROM subscribers WHERE email = ?), ?)',
			$email,
			$reminder
		);
		return new MySqlRemindedPassword(
			$reminder,
			$this->database,
			$this->cipher
		);
	}
}