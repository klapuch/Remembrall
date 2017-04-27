<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Access;
use Klapuch\Storage;

/**
 * Participants owned by the author
 */
final class OwnedParticipants implements Participants {
	private $author;
	private $database;

	public function __construct(Access\User $author, \PDO $database) {
		$this->author = $author;
		$this->database = $database;
	}

	public function invite(int $subscription, string $email): Invitation {
		return new ParticipantInvitation(
			(new Storage\ParameterizedQuery(
				$this->database,
				'INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) VALUES
				(?, ?, ?, NOW(), FALSE, NULL)
				ON CONFLICT (email, subscription_id)
				DO UPDATE SET invited_at = NOW()
				RETURNING code',
				[$email, $subscription, bin2hex(random_bytes(32))]
			))->field(),
			$this->database
		);
	}

	public function kick(int $subscription, string $email): void {
		(new Storage\ParameterizedQuery(
			$this->database,
			'DELETE FROM participants
			WHERE email = ?
			AND subscription_id = ?',
			[$email, $subscription]
		))->execute();
	}

	public function all(): \Iterator {
		$participants = (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT participants.email, subscription_id, invited_at, accepted, decided_at
			FROM participants
			INNER JOIN subscriptions ON subscriptions.id = participants.subscription_id
			INNER JOIN users ON users.id = subscriptions.user_id
			WHERE user_id = ?
			ORDER BY decided_at DESC',
			[$this->author->id()]
		))->execute();
		foreach ($participants as $participant)
			yield new ConstantParticipant($participant);
	}
}