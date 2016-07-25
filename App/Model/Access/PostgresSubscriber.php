<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Dibi;

/**
 * Subscriber in the Postgres database
 */
final class PostgresSubscriber implements Subscriber {
	private $id;
	private $database;

	public function __construct(int $id, Dibi\Connection $database) {
		$this->id = $id;
		$this->database = $database;
	}

	public function email(): string {
		return $this->database->fetchSingle(
			'SELECT email FROM subscribers WHERE id = ?',
			$this->id()
		);
	}

	public function id(): int {
		return $this->id;
	}
}