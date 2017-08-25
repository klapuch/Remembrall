<?php
declare(strict_types = 1);
namespace Remembrall\TestCase;

use Remembrall\Misc;

trait Database {
	/** @var \PDO */
	protected $database;

	/** @var string[] */
	protected $credentials;

	/** @var \Remembrall\Misc\Databases */
	private $databases;

	protected function setUp(): void {
		parent::setUp();
		$this->credentials = parse_ini_file(__DIR__ . '/.config.local.ini', true);
		$this->databases = new Misc\RandomDatabases($this->credentials);
		$this->database = $this->databases->create();
	}

	protected function tearDown(): void {
		parent::tearDown();
		$this->database = null;
		$this->databases->drop();
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
}