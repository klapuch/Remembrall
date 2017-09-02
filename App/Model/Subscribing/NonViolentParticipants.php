<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Access;
use Klapuch\Storage;

/**
 * All the non-violent participants
 */
final class NonViolentParticipants implements Participants {
	private $author;
	private $database;

	public function __construct(Access\User $author, \PDO $database) {
		$this->author = $author;
		$this->database = $database;
	}

	public function invite(int $subscription, string $email): Invitation {
		if ($this->harassed($subscription, $email)) {
			throw new \UnexpectedValueException(
				sprintf(
					'"%s" declined your invitation too many times',
					$email
				)
			);
		}
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

	public function all(): \Iterator {
		$participants = (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT participants.id, participants.email, subscription_id, is_invitation_harassed(
				participants.id,
				participants.email
			) AS harassed,
			invited_at, accepted, decided_at
			FROM participants
			INNER JOIN subscriptions ON subscriptions.id = participants.subscription_id
			INNER JOIN users ON users.id = subscriptions.user_id
			WHERE user_id = ?
			ORDER BY decided_at DESC',
			[$this->author->id()]
		))->execute();
		foreach ($participants as $participant) {
			yield new InvitedParticipant(
				new Storage\MemoryPDO($this->database, $participant),
				$participant['subscription_id'],
				$participant['email']
			);
		}
	}

	/**
	 * Was the participant asked too many times for invitation?
	 * @param int $subscription
	 * @param string $email
	 * @return bool
	 */
	private function harassed(int $subscription, string $email): bool {
		return (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT is_invitation_harassed(?, ?)',
			[$subscription, $email]
		))->field();
	}
}