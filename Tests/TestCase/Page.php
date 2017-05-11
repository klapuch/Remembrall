<?php
declare(strict_types = 1);
namespace Remembrall\TestCase;

use Klapuch\Csrf;
use Klapuch\Storage;

abstract class Page extends Database {
	protected $configuration;
	/** @var \PDO */
	protected $database;

	protected function setUp(): void {
		parent::setUp();
		$_POST[Csrf\Protection::NAME] = $_SESSION[Csrf\Protection::NAME] = '8PfBgonTZ9YcodKUzQ==';
		$credentials = parse_ini_file(__DIR__ . '/.database.ini', true)['POSTGRES'];
		$this->configuration = [
			'DATABASE' => $credentials,
			'PROPRIETARY_SESSIONS' => [],
			'KEYS' => ['password' => '\x32\x0d\xe7\x7b\x06\xa3\x4a\xff\x39\x4d\xcf\xb0\xac\xf5\x22\x85'],
		];
		$this->database = new Storage\SafePDO(
			$credentials['dsn'],
			$credentials['user'],
			$credentials['password']
		);
	}
}