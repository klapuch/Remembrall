<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Output;
use Klapuch\Storage;

final class ParticipantInvitation implements Invitation {
	private $code;
	private $database;

	public function __construct(string $code, \PDO $database) {
		$this->code = $code;
		$this->database = $database;
	}

	public function accept(): void {
		if (!$this->known($this->code) || $this->accepted($this->code)) {
			throw new \Remembrall\Exception\NotFoundException(
				'The invitation is accepted or does not exist'
			);
		}
		(new Storage\ParameterizedQuery(
			$this->database,
			'UPDATE participants
			SET accepted = TRUE, decided_at = NOW()
			WHERE code = ?',
			[$this->code]
		))->execute();
	}

	public function deny(): void {
		if (!$this->known($this->code) || $this->decided($this->code)) {
			throw new \Remembrall\Exception\NotFoundException(
				'The invitation is denied or does not exist'
			);
		}
		(new Storage\ParameterizedQuery(
			$this->database,
			'UPDATE participants
			SET decided_at = NOW()
			WHERE code = ?',
			[$this->code]
		))->execute();
	}

	public function print(Output\Format $format): Output\Format {
		if (!$this->known($this->code) || $this->decided($this->code)) {
			throw new \Remembrall\Exception\NotFoundException(
				'The invitation is denied or does not exist'
			);
		}
		$invitation = (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT email, code, (
				SELECT email
				FROM users
				WHERE id = subscriptions.user_id
			) AS author, expression, page_url AS url
			FROM participants
			INNER JOIN subscriptions ON subscriptions.id = participants.subscription_id
			INNER JOIN parts ON parts.id = subscriptions.part_id
			WHERE code = :code',
			['code' => $this->code]
		))->row();
		return $format->with('email', $invitation['email'])
			->with('code', $invitation['code'])
			->with('author', $invitation['author'])
			->with('expression', $invitation['expression'])
			->with('url', $invitation['url']);
	}

	private function accepted(string $code): bool {
		return (bool) (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT 1
			FROM participants
			WHERE code = ?
			AND accepted = TRUE',
			[$code]
		))->field();
	}

	private function decided(string $code): bool {
		return (bool) (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT 1
			FROM participants
			WHERE code = ?
			AND decided_at IS NOT NULL',
			[$code]
		))->field();
	}

	private function known(string $code): bool {
		return (bool) (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT 1
			FROM participants
			WHERE code = ?',
			[$code]
		))->field();
	}
}