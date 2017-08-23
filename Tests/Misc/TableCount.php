<?php
declare(strict_types = 1);
namespace Remembrall\Misc;

use Tester\Assert;

final class TableCount implements Assertion {
	private $database;
	private $table;
	private $count;

	public function __construct(\PDO $database, string $table, int $count) {
		$this->database = $database;
		$this->table = $table;
		$this->count = $count;
	}

	public function assert(): void {
		Assert::same(
			$this->count,
			$this->database->query(
				sprintf(
					'SELECT COUNT(*) FROM %s',
					$this->table
				)
			)->fetchColumn()
		);
	}
}