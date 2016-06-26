<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Dibi;
use Remembrall\Model\Security;

/**
 * Changes password in the database
 */
final class MySqlRemindedPassword implements RemindedPassword {
	private $reminder;
	private $database;
	private $cipher;

	public function __construct(
		string $reminder,
		Dibi\Connection $database,
		Security\Cipher $cipher
	) {
		$this->reminder = $reminder;
		$this->database = $database;
		$this->cipher = $cipher;
	}

	public function change(string $password) {
		$this->database->query(
			'UPDATE subscribers
			SET `password` = ?
			WHERE ID = (
				SELECT subscriber_id
                FROM forgotten_passwords
                WHERE reminder = ?
            )',
			$this->cipher->encrypt($password),
			$this->reminder
		);
		$this->database->query(
			'UPDATE forgotten_passwords
			SET used = 1
			WHERE reminder = ?',
			$this->reminder
		);
	}
}