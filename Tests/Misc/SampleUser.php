<?php
declare(strict_types = 1);
namespace Remembrall\Misc;

final class SampleUser implements Sample {
	private const ROLES = ['guest', 'member', 'admin'];
	private $database;
	private $user;

	public function __construct(\PDO $database, array $user = []) {
		$this->database = $database;
		$this->user = $user;
	}

	public function try(): void {
		$stmt = $this->database->prepare(
			'INSERT INTO users (email, password, role) VALUES
			(?, ?, ?)'
		);
		$stmt->execute(
			[
				$this->user['email'] ?? sprintf(
					'%s@%s.com',
					substr(uniqid('', true), -mt_rand(1, 10)),
					substr(uniqid('', true), -mt_rand(1, 20))
				),
				$this->user['password'] ?? bin2hex(random_bytes(20)),
				$this->user['role'] ?? self::ROLES[array_rand(self::ROLES)],
			]
		);
	}
}