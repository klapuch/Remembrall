<?php
declare(strict_types = 1);
namespace Remembrall\TestCase;

use Klapuch\Storage;
use Tester;

abstract class Database extends Mockery {
	/** @var Storage\Database */
	protected $database;

	protected function setUp() {
		parent::setUp();
		Tester\Environment::lock('database', __DIR__ . '/../Temporary');
		$credentials = parse_ini_file(__DIR__ . '/.database.ini');
		$this->database = new Storage\PDODatabase(
			$credentials['dsn'],
			$credentials['user'],
			$credentials['password']
		);
		$this->prepareDatabase();
	}

	protected function prepareDatabase() {
		/** Template method, suitable for overriding */
	}

	/**
	 * Truncate and restart sequences to the given tables
	 * @param array $tables
	 */
	protected function purge(array $tables) {
		$this->truncate($tables);
		$this->restartSequence($tables);
	}

	/**
	 * Truncate the tables
	 * @param array $tables
	 */
	final protected function truncate(array $tables) {
		$this->database->exec(
			sprintf('TRUNCATE %s', implode(',', $tables))
		);
	}

	/**
	 * Restart sequences to the given tables
	 * @param array $tables
	 */
	final protected function restartSequence(array $tables) {
		foreach($tables as $table) {
			$this->database->exec(
				sprintf('ALTER SEQUENCE %s_id_seq RESTART', $table)
			);
		}
	}
}