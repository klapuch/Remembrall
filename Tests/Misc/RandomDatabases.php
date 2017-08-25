<?php
declare(strict_types = 1);
namespace Remembrall\Misc;

use Klapuch\Storage;

final class RandomDatabases implements Databases {
	private $credentials;
	private $name;

	public function __construct(array $credentials) {
		$this->credentials = $credentials;
		$this->name = 'test_' . bin2hex(random_bytes(20));
	}

	public function create(): \PDO {
		$this->database('postgres')->exec(
			sprintf(
				'CREATE DATABASE %s WITH TEMPLATE %s',
				$this->name,
				$this->credentials['POSTGRES']['template']
			)
		);
		return $this->database($this->name);
	}

	public function drop(): void {
		(new Storage\SafePDO(
			sprintf($this->credentials['POSTGRES']['dsn'], 'postgres'),
			$this->credentials['POSTGRES']['user'],
			$this->credentials['POSTGRES']['password']
		))->exec(sprintf('DROP DATABASE %s', $this->name));
	}

	private function database(string $name): \PDO {
		return new Storage\SafePDO(
			sprintf($this->credentials['POSTGRES']['dsn'], $name),
			$this->credentials['POSTGRES']['user'],
			$this->credentials['POSTGRES']['password']
		);
	}
}