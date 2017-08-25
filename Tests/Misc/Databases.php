<?php
declare(strict_types = 1);
namespace Remembrall\Misc;

interface Databases {
	/**
	 * Create a new database
	 * @return \PDO
	 */
	public function create(): \PDO;

	/**
	 * Drop the database
	 * @return void
	 */
	public function drop(): void;
}