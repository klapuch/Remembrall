<?php
namespace Remembrall\Model\Access;

use Klapuch\{
	Storage, Encryption
};
use Nette\Security;
use Nette\Security\AuthenticationException;

final class Authenticator implements Security\IAuthenticator {
	private $database;
	private $cipher;

	public function __construct(
		Storage\Database $database,
		Encryption\Cipher $cipher
	) {
		$this->database = $database;
		$this->cipher = $cipher;
	}

	public function authenticate(array $credentials) {
		list($plainUsername, $plainPassword) = $credentials;
		$row = $this->database->fetch(
			'SELECT id, password
			 FROM subscribers  
			 WHERE email IS NOT DISTINCT FROM ?',
			[$plainUsername]
		);
		if(!$this->exists($row)) {
			throw new AuthenticationException(
				sprintf('Email "%s" does not exist', $plainUsername)
			);
		} elseif(!$this->cipher->decrypt($plainPassword, $row['password'])) {
			throw new AuthenticationException('Wrong password');
		}
		if($this->cipher->deprecated($row->password))
			$this->rehash($plainPassword, $row['id']);
		return new Security\Identity($row['id']);
	}

	/**
	 * Does the record exist?
	 * @param int|null $id
	 * @return bool
	 */
	private function exists($id): bool {
		return $id !== null;
	}

	/**
	 * Rehash the password with the newest one
	 * @param string $password
	 * @param int $id
	 */
	private function rehash(string $password, int $id) {
		$this->database->query(
			'UPDATE subscribers
			SET password = ?
			WHERE id IS NOT DISTINCT FROM ?',
			[$this->cipher->encrypt($password), $id]
		);
	}
}