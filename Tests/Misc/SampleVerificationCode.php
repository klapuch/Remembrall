<?php
declare(strict_types = 1);
namespace Remembrall\Misc;

final class SampleVerificationCode implements Sample {
	private $database;
	private $verificationCode;

	public function __construct(\PDO $database, array $verificationCode = []) {
		$this->database = $database;
		$this->verificationCode = $verificationCode;
	}

	public function try(): void {
		$stmt = $this->database->prepare(
			sprintf(
				"INSERT INTO verification_codes (user_id, code, used, used_at) VALUES
            	(?, ?, '%s', NOW())",
				($this->verificationCode['used'] ?? mt_rand(0, 1)) ? 't' : 'f'
			)
		);
		$stmt->execute(
			[
				$this->verificationCode['user_id'] ?? $this->verificationCode['user'] ?? mt_rand(),
				$this->verificationCode['code'] ?? bin2hex(random_bytes(10)),
			]
		);
	}
}