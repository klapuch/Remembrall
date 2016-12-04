<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Klapuch\Storage;
use Remembrall\Exception\NotFoundException;

/**
 * Already registered subscriber
 */
final class RegisteredSubscriber implements Subscriber {
	private $id;
	private $database;

	public function __construct(int $id, Storage\Database $database) {
		$this->id = $id;
		$this->database = $database;
	}

	public function email(): string {
		return (string)$this->database->fetchColumn(
			'SELECT email
            FROM users
            WHERE id IS NOT DISTINCT FROM ?',
			[$this->id()]
		);
	}

	public function id(): int {
		if($this->registered($this->id))
			return $this->id;
		throw new NotFoundException(
			sprintf('User id "%d" does not exist', $this->id)
		);
	}

	/**
	 * Is the user already registered?
	 * @param int $id
	 * @return bool
	 */
	private function registered(int $id): bool {
		return (bool)$this->database->fetchColumn(
			'SELECT 1
			FROM users
			WHERE id IS NOT DISTINCT FROM ?',
			[$id]
		);
	}
}