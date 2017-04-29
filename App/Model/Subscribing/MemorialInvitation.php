<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Output;
use Klapuch\Storage;

final class MemorialInvitation implements Invitation {
	private $subscription;
	private $email;
	private $database;

	public function __construct(int $subscription, string $email, \PDO $database) {
		$this->subscription = $subscription;
		$this->email = $email;
		$this->database = $database;
	}

	public function accept(): void {
		throw new \LogicException('Memorial invitation can not be accepted');
	}

	public function decline(): void {
		throw new \LogicException('Memorial invitation can not be declined');
	}

	public function print(Output\Format $format): Output\Format {
		$invitation = (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT users.email AS author, expression, page_url AS url
			FROM participants
			INNER JOIN subscriptions ON subscriptions.id = participants.subscription_id
			INNER JOIN parts ON parts.id = subscriptions.part_id
			INNER JOIN users ON users.id = subscriptions.user_id
			WHERE participants.subscription_id = ?
			AND participants.email = ?',
			[$this->subscription, $this->email]
		))->row();
		return $format->with('author', $invitation['author'])
			->with('expression', $invitation['expression'])
			->with('url', $invitation['url']);
	}
}