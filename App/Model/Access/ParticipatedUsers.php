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
				$subscriptions = $this->subscriptions($email);
				(new Storage\ParameterizedQuery(
					$this->database,
					sprintf(
						'INSERT INTO subscriptions (user_id, part_id, interval, last_update, snapshot)
						SELECT ?, part_id, interval, last_update, snapshot
						FROM subscriptions
						WHERE id IN (%s)',
						$this->placeholders($subscriptions)
					),
					array_merge([$user->id()], $subscriptions)
				))->execute();
				return $user;
			}
		);
	}

	private function subscriptions(string $email): array {
		$participants = (new Storage\ParameterizedQuery(
			$this->database,
			'DELETE FROM participants
			WHERE email = ?
			AND accepted = TRUE
			RETURNING id, subscription_id',
			[$email]
		))->rows() + [['id' => -1, 'subscription_id' => -1]];
		(new Storage\ParameterizedQuery(
			$this->database,
			sprintf(
				'DELETE FROM invitation_attempts
				WHERE participant_id IN (%s)',
				$this->placeholders($participants)
			),
			array_column($participants, 'id')
		))->execute();
		return array_column($participants, 'subscription_id');
	}

	/**
	 * Placeholders for SQL query in format: ?,?,?
	 * @param array $values
	 * @return string
	 */
	private function placeholders(array $values): string {
		return implode(',', array_fill(0, count($values) ?: 1, '?'));
	}
}