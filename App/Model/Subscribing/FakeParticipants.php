<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

/**
 * Fake
 */
final class FakeParticipants implements Participants {
	public function invite(int $subscription, string $email): void {
	}

	public function kick(int $subscription, string $email): void {
	}

	public function all(): \Iterator {
	}
}