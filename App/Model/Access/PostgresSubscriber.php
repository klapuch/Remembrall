<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Klapuch\Storage;

/**
 * Subscriber in the Postgres database
 */
final class PostgresSubscriber implements Subscriber {
	private $id;
	private $database;

	public function __construct(int $id, Storage\Database $database) {
		$this->id = $id;
		$this->database = $database;
	}

	public function email(): string {
		return $this->database->fetchColumn(
			'SELECT email FROM subscribers WHERE id = ?',
			[$this->id()]
		);
	}

	public function id(): int {
		return $this->id;
	}
}