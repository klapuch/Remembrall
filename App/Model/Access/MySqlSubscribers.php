<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Dibi;
use Remembrall\Model\Security;
use Remembrall\Exception;

/**
 * All subscribers from the database
 */
final class MySqlSubscribers implements Subscribers {
	private $database;
	private $cipher;

	public function __construct(
		Dibi\Connection $database,
		Security\Cipher $cipher
	) {
		$this->database = $database;
		$this->cipher = $cipher;
	}

	public function register(string $email, string $password): Subscriber {
		try {
			$this->database->query(
				'INSERT INTO subscribers (email, `password`) VALUES (?, ?)',
				$email,
				$this->cipher->encrypt($password)
			);
			return new MySqlSubscriber(
				$this->database->insertId(),
				$this->database
			);
		} catch(Dibi\UniqueConstraintViolationException $ex) {
			throw new Exception\DuplicateException(
				sprintf('Email "%s" already exists', $email)
			);
		}
	}
}