<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Storage;

/**
 * Participants which are not registered in the current system
 */
final class GuestParticipants implements Participants {
	private $origin;
	private $database;

	public function __construct(Participants $origin, \PDO $database) {
		$this->origin = $origin;
		$this->database = $database;
	}

	public function invite(int $subscription, string $email): Invitation {
		if ($this->registered($email)) {
			throw new \UnexpectedValueException(
				sprintf(
					'Email "%s" is registered and can not be participant',
					$email
				)
			);
		}
		return $this->origin->invite($subscription, $email);
	}

	public function kick(int $subscription, string $email): void {
		$this->origin->kick($subscription, $email);
	}

	public function all(): \Iterator {
		return $this->origin->all();
	}

	/**
	 * Is the email registered?
	 * @param string $email
	 * @return bool
	 */
	private function registered(string $email): bool {
		return (bool) (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT 1
			FROM users
			WHERE LOWER(email) = LOWER(?)',
			[$email]
		))->field();
	}
}