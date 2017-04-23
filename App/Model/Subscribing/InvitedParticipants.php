<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Output;
use Klapuch\Storage;

/**
 * Participants in invite phase
 */
final class InvitedParticipants implements Participants {
	private $origin;
	private $subscription;
	private $database;

	public function __construct(
		Participants $origin,
		int $subscription,
		\PDO $database
	) {
		$this->origin = $origin;
		$this->subscription = $subscription;
		$this->database = $database;
	}

	public function invite(string $email): void {
		if ($this->accepted($email, $this->subscription)) {
			throw new \Remembrall\Exception\DuplicateException(
				sprintf('Email "%s" is already your participant', $email)
			);
		}
		$this->origin->invite($email);
	}

	public function kick(string $email): void {
		if (!$this->invited($email, $this->subscription)) {
			throw new \Remembrall\Exception\NotFoundException(
				sprintf('Email "%s" is not your participant', $email)
			);
		}
		$this->origin->kick($email);
	}

	public function print(Output\Format $format): \Iterator {
		return $this->origin->print($format);
	}

	private function accepted(string $email, int $subscription): bool {
		return (bool) (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT 1
			FROM participants
			WHERE email = ?
			AND subscription_id = ?
			AND accepted = TRUE',
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