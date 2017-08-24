<?php
declare(strict_types = 1);
namespace Remembrall\Misc;

final class SampleParticipant implements Sample {
	private $database;
	private $participant;

	public function __construct(\PDO $database, array $participant = []) {
		$this->database = $database;
		$this->participant = $participant;
	}

	public function try(): void {
		$stmt = $this->database->prepare(
			sprintf(
				"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) 
				VALUES (?, ?, ?, %s, '%s', %s)",
				$this->participant['invited_at'] ?? 'NOW()',
				($this->participant['accepted'] ?? mt_rand(0, 1)) ? 't' : 'f',
				$this->participant['decided_at'] ?? 'NOW()'
			)
		);
		$stmt->execute(
			[
				$this->participant['email'] ?? sprintf(
					'%s@gmail.com',
					substr(uniqid('', true), -mt_rand(1, 10))
				),
				$this->participant['subscription_id'] ?? $this->participant['subscription'] ?? mt_rand(),
				$this->participant['code'] ?? bin2hex(random_bytes(10)),
			]
		);
	}
}