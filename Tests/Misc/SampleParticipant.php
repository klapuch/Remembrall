<?php
declare(strict_types = 1);
namespace Remembrall\Misc;

final class SampleParticipant implements Sample {
	private $database;
	private $email;
	private $subscription;
	private $code;

	public function __construct(
		\PDO $database,
		int $subscription,
		string $email = null,
		string $code = null
	) {
		$this->database = $database;
		$this->subscription = $subscription;
		$this->email = $email;
		$this->code = $code;
	}

	public function try(): void {
		$stmt = $this->database->prepare(
			'INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) 
			VALUES (?, ?, ?, NOW(), FALSE, NULL)'
		);
		$stmt->execute(
			[
				$this->email ?: sprintf(
					'%s@gmail.com',
					substr(uniqid('', true), -mt_rand(1, 10))
				),
				$this->subscription,
				$this->code ?: bin2hex(random_bytes(10)),
			]
		);
	}
}