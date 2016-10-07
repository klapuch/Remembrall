<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Klapuch\{
    Storage, Encryption
};
use Remembrall\Exception\DuplicateException;

/**
 * Collection of unique subscribers
 */
final class UniqueSubscribers implements Subscribers {
	private $database;
	private $cipher;

	public function __construct(
		Storage\Database $database,
		Encryption\Cipher $cipher
	) {
		$this->database = $database;
		$this->cipher = $cipher;
	}

	public function register(string $email, string $password): Subscriber {
		try {
			$id = $this->database->fetchColumn(
				'INSERT INTO subscribers(email, password) VALUES
				(?, ?) RETURNING id',
				[$email, $this->cipher->encrypt($password)]
			);
			return new RegisteredSubscriber($id, $this->database);
		} catch(Storage\UniqueConstraint $ex) {
			throw new DuplicateException(
				sprintf('Email "%s" already exists', $email),
				$ex->getCode(),
				$ex
			);
		}
	}
}