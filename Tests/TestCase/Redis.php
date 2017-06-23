<?php
declare(strict_types = 1);
namespace Remembrall\TestCase;

use Predis;
use Tester;

trait Redis {
	/** @var \Predis\Client */
	protected $redis;

	/** @var string[] */
	protected $credentials;

	protected function setUp(): void {
		parent::setUp();
		Tester\Environment::lock('redis', __DIR__ . '/../temp');
		$this->credentials = parse_ini_file(__DIR__ . '/.config.local.ini', true);
		$this->redis = new Predis\Client($this->credentials['REDIS']['uri']);
		$this->redis->flushall();
	}
}