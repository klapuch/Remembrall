<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Output;
use Klapuch\Storage;

/**
 * Participants connected to one particular subscription
 */
final class ConnectedParticipants implements Participants {
	private $subscription;
	private $database;

	public function __construct(int $subscription, \PDO $database) {
		$this->subscription = $subscription;
		$this->database = $database;
	}

	public function invite(string $email): void {
		(new Storage\ParameterizedQuery(
			$this->database,
			'INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			(?, ?, ?, NOW(), FALSE, NULL)
			ON CONFLICT (email, subscription_id)
			DO UPDATE SET invited_at = NOW()',
			[$email, $this->subscription, bin2hex(random_bytes(32))]
		))->execute();
	}

	public function kick(string $email): void {
		(new Storage\ParameterizedQuery(
			$this->database,
			'DELETE FROM participants
			WHERE email = ?
			AND subscription_id = ?',
			[$email, $this->subscription]
		))->execute();
	}

	public function print(Output\Format $format): \Iterator {
		$participants = (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT email, subscription_id, invited_at, accepted, decided_at
			FROM participants
			WHERE subscription_id = ?',
			[$this->subscription]
		))->execute();
		foreach ($participants as $participant) {
			yield $format->with('email', $participant['email'])
				->with('subscription_id', $participant['subscription_id'])
				->with('invited_at', $participant['invited_at'])
				->with('accepted', $participant['accepted'])
				->with('decided_at', $participant['decided_at']);
		}
	}
}