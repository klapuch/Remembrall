<?php
declare(strict_types = 1);
namespace Remembrall\TestCase;

use Klapuch\Storage;

abstract class Page extends Database {
	protected $configuration;
	/** @var \PDO */
	protected $database;

	protected function setUp(): void {
		parent::setUp();
		$_SESSION = [];
		$credentials = parse_ini_file(__DIR__ . '/.database.ini', true)['POSTGRES'];
		$this->configuration = [
			'DATABASE' => $credentials,
			'PROPRIETARY_SESSIONS' => [],
		];
		$this->database = new Storage\SafePDO(
			$credentials['dsn'],
			$credentials['user'],
			$credentials['password']
		);
	}
}