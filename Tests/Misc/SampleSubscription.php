<?php
declare(strict_types = 1);
namespace Remembrall\Misc;

final class SampleSubscription implements Sample {
	private $database;
	private $subscription;

	public function __construct(\PDO $database, array $subscription) {
		$this->database = $database;
		$this->subscription = $subscription;
	}

	public function try(): void {
		$stmt = $this->database->prepare(
			'INSERT INTO subscriptions (part_id, user_id, interval, last_update, snapshot) VALUES
			(?, ?, ?, ?, ?)'
		);
		$stmt->execute(
			[
				$this->subscription['part'] ?? $this->subscription['part_id'] ?? mt_rand(),
				$this->subscription['user'] ?? $this->subscription['user_id'] ?? mt_rand(),
				$this->subscription['interval'] ?? sprintf('PT%dM', mt_rand(30, 100)),
				$this->subscription['last_update'] ?? sprintf('199%1$d-0%1$d-0%1$d', mt_rand(1, 9)),
				$this->subscription['snapshot'] ?? bin2hex(random_bytes(10)),
			]
		);
	}
}