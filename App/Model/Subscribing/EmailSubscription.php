<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\{
	Time, Output
};
use Nette\Mail;

/**
 * Subscriptions sending to an email
 */
final class EmailSubscription implements Subscription {
	private const SENDER = 'Remembrall <remembrall@remembrall.org>';
	private const TEMPLATES = __DIR__ . '/../../Page/templates/Email/Subscribing',
		SUBJECT = self::TEMPLATES . '/subject.xsl',
		CONTENT = self::TEMPLATES . '/content.xsl',
		SCHEMA = self::TEMPLATES . '/constraint.xsd';
	private $origin;
	private $mailer;
	private $recipient;
	private $subscription;

	public function __construct(
		Subscription $origin,
		Mail\IMailer $mailer,
		string $recipient,
		array $subscription
	) {
		$this->origin = $origin;
		$this->mailer = $mailer;
		$this->recipient = $recipient;
		$this->subscription = $subscription;
	}

	public function cancel(): void {
		$this->origin->cancel();
	}

	public function edit(Time\Interval $interval): void {
		$this->origin->edit($interval);
	}

	public function notify(): void {
		$this->origin->notify();
		$this->mailer->send(
			(new Mail\Message())
			->setFrom(self::SENDER)
			->addTo($this->recipient)
			->setSubject(
				(new Output\XsltTemplate(
					self::SUBJECT,
					new Output\ValidXml(
						new Output\Xml($this->subscription, 'part'),
						self::SCHEMA
					)
				))->render()
			)
			->setHtmlBody(
				(new Output\XsltTemplate(
					self::CONTENT,
					new Output\ValidXml(
						new Output\Xml($this->subscription, 'part'),
						self::SCHEMA
					)
				))->render()
			)
		);
	}
}