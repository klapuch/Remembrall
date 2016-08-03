<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Dibi;
use Klapuch\Encryption;

/**
 * Reminded password stored in the Postgres database
 */
final class PostgresRemindedPassword implements RemindedPassword {
	private $reminder;
	private $database;
	private $cipher;

	public function __construct(
		string $reminder,
		Dibi\Connection $database,
		Encryption\Cipher $cipher
	) {
		$this->reminder = $reminder;
		$this->database = $database;
		$this->cipher = $cipher;
	}

	public function change(string $password) {
		$this->database->query(
			'UPDATE subscribers
			SET password = ?
			WHERE id = (
				SELECT subscriber_id
                FROM forgotten_passwords
                WHERE reminder IS NOT DISTINCT FROM ?
            )',
			$this->cipher->encrypt($password),
			$this->reminder
		);
		$this->database->query(
			'UPDATE forgotten_passwords
			SET used = TRUE
			WHERE reminder IS NOT DISTINCT FROM ?',
			$this->reminder
		);
	}
}