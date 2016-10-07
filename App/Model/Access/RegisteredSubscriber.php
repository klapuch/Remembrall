<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Klapuch\Storage;

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
		return $this->database->fetchColumn(
            'SELECT email
            FROM subscribers
            WHERE id IS NOT DISTINCT FROM ?',
			[$this->id()]
		);
	}

	public function id(): int {
		return $this->id;
	}
}