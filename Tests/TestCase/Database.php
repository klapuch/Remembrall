<?php
declare(strict_types = 1);
namespace Remembrall\TestCase;

use Klapuch\Storage;
use Tester;

abstract class Database extends Mockery {
	/** @var \PDO */
	protected $database;

	protected function setUp(): void {
		parent::setUp();
		Tester\Environment::lock('database', __DIR__ . '/../temp');
		$credentials = parse_ini_file(__DIR__ . '/.database.ini', true);
		$this->database = new Storage\SafePDO(
			$credentials['POSTGRES']['dsn'],
			$credentials['POSTGRES']['user'],
			$credentials['POSTGRES']['password']
		);
		$this->prepareDatabase();
	}

	protected function prepareDatabase(): void {
		/** Template method, suitable for overriding */
	}

	/**
	 * Truncate and restart sequences to the given tables
	 * @param array $tables
	 */
	protected function purge(array $tables): void {
		$this->truncate($tables);
		$this->restartSequence($tables);
	}

	/**
	 * Truncate the tables
	 * @param array $tables
	 */
	final protected function truncate(array $tables): void {
		$this->database->exec(sprintf('TRUNCATE %s', implode(',', $tables)));
	}

	/**
	 * Restart sequences to the given tables
	 * @param array $tables
	 */
	final protected function restartSequence(array $tables): void {
		foreach ($tables as $table) {
			$this->database->exec(
				sprintf('ALTER SEQUENCE %s_id_seq RESTART', $table)
			);
		}
	}
}