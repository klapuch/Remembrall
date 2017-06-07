<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

/**
 * Fake
 */
final class FakeParticipants implements Participants {
	private $invitation;

	public function __construct(Invitation $invitation = null) {
		$this->invitation = $invitation;
	}

	public function invite(int $subscription, string $email): Invitation {
		return $this->invitation;
	}

	public function kick(int $subscription, string $email): void {
	}

	public function all(): \Iterator {
	}
}