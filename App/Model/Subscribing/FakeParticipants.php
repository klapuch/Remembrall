<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Output;

/**
 * Fake
 */
final class FakeParticipants implements Participants {
	public function invite(string $email): void {
	}

	public function kick(string $email): void {
	}

	public function print(Output\Format $format): \Iterator {
	}
}