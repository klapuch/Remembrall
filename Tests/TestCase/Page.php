<?php
declare(strict_types = 1);
namespace Remembrall\TestCase;

use Klapuch\Csrf;
use Remembrall\TestCase;

trait Page {
	use TestCase\Database {
		Database::setUp as databaseSetUp;
	}

	protected $configuration;

	protected function setUp(): void {
		parent::setUp();
		$this->databaseSetUp();
		$_POST = [];
		$_POST[Csrf\Protection::NAME] = $_SESSION[Csrf\Protection::NAME] = '8PfBgonTZ9YcodKUzQ==';
		$this->configuration = [
			'DATABASE' => $this->credentials['POSTGRES'],
			'REDIS' => $this->credentials['REDIS'],
			'PROPRIETARY_SESSIONS' => [],
			'KEYS' => ['password' => '\x32\x0d\xe7\x7b\x06\xa3\x4a\xff\x39\x4d\xcf\xb0\xac\xf5\x22\x85'],
		];
	}
}