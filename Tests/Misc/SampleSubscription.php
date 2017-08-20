<?php
declare(strict_types = 1);
namespace Remembrall\Misc;

use Klapuch\Access;

final class SampleSubscription implements Sample {
	private $database;
	private $user;
	private $part;

	public function __construct(
		\PDO $database,
		Access\User $user,
		int $part
	) {
		$this->database = $database;
		$this->user = $user;
		$this->part = $part;
	}

	public function try(): void {
		$stmt = $this->database->prepare(
			'INSERT INTO subscriptions (part_id, user_id, interval, last_update, snapshot) VALUES
			(?, ?, ?, ?, ?)'
		);
		$stmt->execute(
			[
				$this->part,
				$this->user->id(),
				sprintf('PT%dM', mt_rand(30, 100)),
				sprintf('199%1$d-0%1$d-0%1$d', mt_rand(1, 9)),
				bin2hex(random_bytes(10)),
			]
		);
	}
}