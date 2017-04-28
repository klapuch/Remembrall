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
		(new Storage\ParameterizedQuery(
			$this->database,
			'UPDATE participants
			SET accepted = TRUE, decided_at = NOW()
			WHERE code = ?',
			[$this->code]
		))->execute();
	}

	public function decline(): void {
		(new Storage\ParameterizedQuery(
			$this->database,
			'UPDATE participants
			SET decided_at = NOW()
			WHERE code = ?',
			[$this->code]
		))->execute();
	}

	public function print(Output\Format $format): Output\Format {
		$invitation = (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT participants.email, code,
			users.email AS author,
			expression, page_url AS url
			FROM participants
			INNER JOIN subscriptions ON subscriptions.id = participants.subscription_id
			INNER JOIN parts ON parts.id = subscriptions.part_id
			INNER JOIN users ON users.id = subscriptions.user_id
			WHERE code = ?',
			[$this->code]
		))->row();
		return $format->with('email', $invitation['email'])
			->with('code', $invitation['code'])
			->with('author', $invitation['author'])
			->with('expression', $invitation['expression'])
			->with('url', $invitation['url']);
	}
}