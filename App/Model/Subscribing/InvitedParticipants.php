<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Storage;

/**
 * Participants in invite phase
 */
final class InvitedParticipants implements Participants {
	private $origin;
	private $database;

	public function __construct(Participants $origin, \PDO $database) {
		$this->origin = $origin;
		$this->database = $database;
	}

	public function invite(int $subscription, string $email): Invitation {
		if ($this->accepted($email, $subscription)) {
			throw new \UnexpectedValueException(
				sprintf('Email "%s" is already your participant', $email)
			);
		}
		return $this->origin->invite($subscription, $email);
	}

	public function kick(int $subscription, string $email): void {
		if (!$this->invited($email, $subscription)) {
			throw new \UnexpectedValueException(
				sprintf('Email "%s" is not your participant', $email)
			);
		}
		$this->origin->kick($subscription, $email);
	}

	public function all(): \Iterator {
		return $this->origin->all();
	}

	private function accepted(string $email, int $subscription): bool {
		return (bool) (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT 1
			FROM participants
			WHERE email = ?
			AND subscription_id = ?
			AND accepted IS TRUE',
			[$email, $subscription]
		))->field();
	}

	private function invited(string $email, int $subscription): bool {
		return (bool) (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT 1
			FROM participants
			WHERE email = ?
			AND subscription_id = ?',
			[$email, $subscription]
		))->field();
	}
}