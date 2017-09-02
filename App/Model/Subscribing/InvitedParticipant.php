<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Output;
use Klapuch\Storage;

/**
 * Participant already invited and added
 */
final class InvitedParticipant implements Participant {
	private $database;
	private $subscription;
	private $email;

	public function __construct(\PDO $database, int $subscription, string $email) {
		$this->database = $database;
		$this->subscription = $subscription;
		$this->email = $email;
	}

	public function print(Output\Format $format): Output\Format {
		$participant = (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT id, email, subscription_id, FALSE AS harassed,
			invited_at, accepted, decided_at
			FROM participants
			WHERE subscription_id = ? AND email = ?',
			[$this->subscription, $this->email]
		))->row();
		return new Output\FilledFormat($format, $participant);
	}

	public function kick(): void {
		if (!$this->invited($this->email, $this->subscription)) {
			throw new \UnexpectedValueException(
				sprintf('Email "%s" is not your participant', $this->email)
			);
		}
		(new Storage\ParameterizedQuery(
			$this->database,
			'DELETE FROM participants
			WHERE email = ?
			AND subscription_id = ?',
			[$this->email, $this->subscription]
		))->execute();
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