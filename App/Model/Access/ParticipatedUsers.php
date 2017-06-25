<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Klapuch\Access;
use Klapuch\Storage;

/**
 * Potential participated users which will be transferred with all subscriptions
 */
final class ParticipatedUsers implements Access\Users {
	private $origin;
	private $database;

	public function __construct(Access\Users $origin, \PDO $database) {
		$this->origin = $origin;
		$this->database = $database;
	}

	public function register(
		string $email,
		string $password,
		string $role
	): Access\User {
		return (new Storage\Transaction($this->database))->start(
			function() use ($email, $password, $role): Access\User {
				$user = $this->origin->register($email, $password, $role);
				(new Storage\ParameterizedQuery(
					$this->database,
					'WITH removed_participants AS (
						DELETE FROM participants
						WHERE email = ?
						AND accepted = TRUE
						RETURNING id, subscription_id
					), removed_invitations AS (
						DELETE FROM invitation_attempts
						WHERE participant_id IN (
							SELECT subscription_id
							FROM removed_participants
						)
						RETURNING participant_id AS subscription_id
					)
					INSERT INTO subscriptions (user_id, part_id, interval, last_update, snapshot)
					SELECT ?, part_id, interval, last_update, snapshot
					FROM subscriptions
					WHERE id IN (
						SELECT subscription_id
						FROM removed_invitations
					)',
					[$email, $user->id()]
				))->execute();
				return $user;
			}
		);
	}
}