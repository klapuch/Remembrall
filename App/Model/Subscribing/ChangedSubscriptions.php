<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Output, Storage, Time, Uri
};
use Nette\Mail;

/**
 * All the subscriptions owned by one particular subscriber
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

	public function iterate(): \Iterator {
		$subscriptions = (new Storage\ParameterizedQuery(
			$this->database,
			"SELECT subscriptions.id, page_url AS url, expression, content, email
			FROM parts
			INNER JOIN subscriptions ON subscriptions.part_id = parts.id
			INNER JOIN users ON users.id = subscriptions.user_id
			WHERE parts.snapshot != subscriptions.snapshot
			AND last_update + INTERVAL '1 SECOND' * SUBSTRING(interval FROM '[0-9]+')::INT < NOW()"
		))->rows();
		foreach($subscriptions as $subscription) {
			yield new EmailSubscription(
				new StoredSubscription($subscription['id'], $this->database),
				$this->mailer,
				$subscription['email'],
				$subscription
			);
		}
	}

	public function print(Output\Format $format): array {
		$subscriptions = (new Storage\ParameterizedQuery(
			$this->database,
			"SELECT subscriptions.id, expression, page_url, interval,
			visited_at, last_update
			FROM parts
			INNER JOIN (
				SELECT part_id, MAX(visited_at) AS visited_at
				FROM part_visits
				GROUP BY part_id
			) AS part_visits ON parts.id = part_visits.part_id
			INNER JOIN subscriptions ON subscriptions.part_id = parts.id
			WHERE parts.snapshot != subscriptions.snapshot
			AND last_update + INTERVAL '1 SECOND' * SUBSTRING(interval FROM '[0-9]+')::INT < NOW()
			ORDER BY visited_at DESC"
		))->rows();
		return array_map(
			function(array $subscription) use ($format): Output\Format {
				return $format->with('expression', $subscription['expression'])
					->with('id', $subscription['id'])
					->with('url', $subscription['page_url'])
					->with(
						'interval',
						(string)new Time\TimeInterval(
							new \DateTimeImmutable($subscription['visited_at']),
							new \DateInterval($subscription['interval'])
						)
					)
					->with('lastUpdate', $subscription['last_update']);
			},
			$subscriptions
		);
	}
}