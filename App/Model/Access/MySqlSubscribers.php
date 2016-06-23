<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Dibi;
use Remembrall\Exception;
use Nette\Security;

/**
 * All subscribers from the database
 */
final class MySqlSubscribers implements Subscribers {
	private $database;

	public function __construct(Dibi\Connection $database) {
		$this->database = $database;
	}

	public function register(string $email, string $password): Subscriber {
		try {
			$this->database->query(
				'INSERT INTO subscribers (email, `password`) VALUES (?, ?)',
				$email,
				Security\Passwords::hash($password)
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