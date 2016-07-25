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

	public function register(string $email, string $password): Subscriber {
		try {
			$id = (int)$this->database->fetchSingle(
				'INSERT INTO subscribers(email, password) VALUES
				(?, ?) RETURNING id',
				$email,
				$this->cipher->encrypt($password)
			);
			return new MySqlSubscriber($id, $this->database);
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