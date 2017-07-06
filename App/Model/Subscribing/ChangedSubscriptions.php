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
		string $language,
		Time\Interval $interval
	): void {
		$this->origin->subscribe($url, $expression, $language, $interval);
	}

	public function all(Dataset\Selection $selection): \Traversable {
		$subscriptions = (new Storage\ParameterizedQuery(
			$this->database,
			$selection->expression(
				"WITH changed_subscriptions AS (
					SELECT readable_subscriptions.id, page_url AS url, (expression).value AS expression, content, email, parts.snapshot, interval_seconds
					FROM parts
					INNER JOIN readable_subscriptions ON readable_subscriptions.part_id = parts.id
					INNER JOIN users ON users.id = readable_subscriptions.user_id
					WHERE parts.snapshot != readable_subscriptions.snapshot
					AND last_update + INTERVAL '1 SECOND' * interval_seconds < NOW()
				)
				SELECT *
				FROM changed_subscriptions
				UNION
				SELECT changed_subscriptions.id, url, expression, content, participants.email, snapshot, interval_seconds
				FROM changed_subscriptions
				LEFT JOIN participants ON participants.subscription_id = changed_subscriptions.id
				WHERE accepted IS TRUE
				ORDER BY email"
			)
		))->rows();
		foreach ($subscriptions as $subscription) {
			yield new EmailSubscription(
				new StoredSubscription(
					$subscription['id'],
					new Storage\MemoryPDO(
						$this->database,
						[
							'content' => $subscription['content'],
							'expression' => $subscription['expression'],
							'url' => $subscription['url'],
						]
					)
				),
				$this->mailer,
				$subscription['email']
			);
		}
	}
}