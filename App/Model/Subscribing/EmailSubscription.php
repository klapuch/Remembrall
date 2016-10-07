<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Nette\Mail;
use Klapuch\{
	Time, Storage, Output
};

final class EmailSubscription implements Subscription {
	private $origin;
	private $id;
	private $mailer;
	private $database;

	public function __construct(
		Subscription $origin,
		int $id,
		Mail\IMailer $mailer,
		Storage\Database $database
	) {
		$this->origin = $origin;
		$this->id = $id;
		$this->mailer = $mailer;
		$this->database = $database;
	}

	public function cancel(): void {
		$this->origin->cancel();
	}

	public function edit(Time\Interval $interval): void {
		$this->origin->edit($interval);
	}

	// TODO, it can not be tested
	public function notify(): void {
		(new Storage\PostgresTransaction($this->database))->start(
			function() {
				$this->origin->notify();
				$subscription = $this->database->fetch(
					'SELECT page_url AS url, expression, content, email
					FROM parts
					INNER JOIN subscriptions ON subscriptions.part_id = parts.id
					INNER JOIN subscribers ON subscribers.id = subscriptions.subscriber_id
					WHERE subscriptions.id = ?',
					[$this->id]
				);
				$this->mailer->send(
					(new Mail\Message())
						->setFrom('Remembrall <remembrall@remembrall.org>')
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
		);
	}
}
