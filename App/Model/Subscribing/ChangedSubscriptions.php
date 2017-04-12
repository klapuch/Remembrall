<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Dataset;
use Klapuch\Storage;
use Klapuch\Time;
use Klapuch\Uri;
use Nette\Mail;

/**
 * All the changed subscriptions
 */
final class ChangedSubscriptions implements Subscriptions {
	private $origin;
	private $mailer;
	private $database;

	public function __construct(
		Subscriptions $origin,
		Mail\IMailer $mailer,
		\PDO $database
	) {
		$this->origin = $origin;
		$this->mailer = $mailer;
		$this->database = $database;
	}

	public function subscribe(
		Uri\Uri $url,
		string $expression,
		Time\Interval $interval
	): void {
		$this->origin->subscribe($url, $expression, $interval);
	}

	public function all(Dataset\Selection $selection): \Traversable {
		$subscriptions = (new Storage\ParameterizedQuery(
			$this->database,
			$selection->expression(
				"SELECT subscriptions.id, page_url AS url, expression, content, email
				FROM parts
				INNER JOIN subscriptions ON subscriptions.part_id = parts.id
				INNER JOIN users ON users.id = subscriptions.user_id
				WHERE parts.snapshot != subscriptions.snapshot
				AND last_update + INTERVAL '1 SECOND' * SUBSTRING(interval FROM '[0-9]+')::INT < NOW()"
			)
		))->rows();
		foreach ($subscriptions as $subscription) {
			yield new EmailSubscription(
				new StoredSubscription($subscription['id'], $this->database),
				$this->mailer,
				$subscription['email'],
				$subscription
			);
		}
	}
}