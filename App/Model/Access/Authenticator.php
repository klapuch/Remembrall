<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Dibi;
use Nette\Security;

final class Authenticator implements Security\IAuthenticator {
	private $database;

	public function __construct(Dibi\Connection $database) {
		$this->database = $database;
	}

	public function authenticate(array $credentials) {
		list($plainUsername, $plainPassword) = $credentials;
		list($id, $password, $role, $username) = $this->database->query(
			'SELECT ID, `password`, role, username
             FROM users
             WHERE username = ?',
			[$plainUsername]
		)->fetch(\PDO::FETCH_NUM);
		if(!$this->exists($id))
			throw new Security\AuthenticationException('Uživatel neexistuje');
		elseif(!$this->activated($id))
			throw new Security\AuthenticationException('Účet není aktivován');
		elseif(!$this->cipher->decrypt($plainPassword, $password))
			throw new Security\AuthenticationException('Nesprávné heslo');
		if($this->cipher->deprecated($password))
			$this->rehash($plainPassword, $id);
		return new Security\Identity($id, $role, ['username' => $username]);
	}

	private function exists($id): bool {
		return (int)$id !== 0;
	}

	private function activated(int $id): bool {
		return (bool)$this->database->fetch(
			'SELECT 1 FROM verification_codes WHERE user_id = ? AND used = 1',
			[$id]
		);
	}

	private function rehash(string $password, int $id) {
		$this->database->query(
			'UPDATE users SET `password` = ? WHERE ID = ?',
			[$this->cipher->encrypt($password), $id]
		);
	}
}