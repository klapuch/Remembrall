<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Dibi;
use Remembrall\Model\Security;
use Remembrall\Exception;

/**
 * Collection of subscribers stored in the MySql database
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

	//TODO: last insert id
	public function register(string $email, string $password): Subscriber {
		try {
			$this->database->query(
				'INSERT INTO subscribers(email, password) VALUES
				(?, ?)',
				$email,
				$this->cipher->encrypt($password)
			);
			return new MySqlSubscriber(
				(int)$this->database->fetchSingle(
					'SELECT ID FROM subscribers WHERE email = ?',
					$email
				),
				$this->database
			);
		} catch(Dibi\UniqueConstraintViolationException $ex) {
			throw new Exception\DuplicateException(
				sprintf('Email "%s" already exists', $email)
			);
		}
	}

	public function iterate(): array {
		throw new \Exception('Not implemented');
	}
}