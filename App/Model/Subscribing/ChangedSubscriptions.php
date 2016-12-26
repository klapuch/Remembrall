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
	private const EMPTY_FORMAT = [];
	private $origin;
	private $mailer;
	private $message;
	private $database;

	public function __construct(
		Subscriptions $origin,
		Mail\IMailer $mailer,
		Mail\Message $message,
		\PDO $database
	) {
		$this->origin = $origin;
		$this->mailer = $mailer;
		$this->message = $message;
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
				new PostgresSubscription($subscription['id'], $this->database),
				$this->mailer,
				$this->message
					->addTo($subscription['email'])
					->setSubject(
						sprintf(
							'Changes occurred on %s page with %s expression',
							$subscription['url'],
							$subscription['expression']
						)
					)
					->setHtmlBody(
						(new Output\XsltTemplate(
							__DIR__ . '/../../Page/templates/Email/subscribing.xsl',
							new Output\Xml($subscription, 'part')
						))->render()
					)
			);
		}
	}

	public function print(Output\Format $format): array {
		$rows = (new Storage\ParameterizedQuery(
			$this->database,
			"SELECT subscriptions.id, expression, page_url AS url, interval,
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
			function(array $row) use ($format): Output\Format {
				return $format->with('expression', $row['expression'])
					->with('id', $row['id'])
					->with('url', $row['url'])
					->with(
						'interval',
						new Time\TimeInterval(
							new \DateTimeImmutable($row['visited_at']),
							new \DateInterval($row['interval'])
						)
					)
					->with('lastUpdate', $row['last_update']);
			},
			$rows
		);
	}
}