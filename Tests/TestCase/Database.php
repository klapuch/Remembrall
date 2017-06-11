<?php
declare(strict_types = 1);
namespace Remembrall\TestCase;

use Klapuch\Storage;
use Tester;

trait Database {
	/** @var \PDO */
	protected $database;

	/** @var string[] */
	protected $credentials;

	protected function setUp(): void {
		parent::setUp();
		Tester\Environment::lock('database', __DIR__ . '/../temp');
		$this->credentials = parse_ini_file(__DIR__ . '/.config.local.ini', true);
		$this->database = new Storage\SafePDO(
			$this->credentials['POSTGRES']['dsn'],
			$this->credentials['POSTGRES']['user'],
			$this->credentials['POSTGRES']['password']
		);
		$this->clear();
	}

	final protected function clear(): void {
		$this->database->exec(
			sprintf(
				"SELECT truncate_tables('%s');
				SELECT restart_sequences()",
				$this->credentials['POSTGRES']['user']
			)
		);
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