<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Output;
use Klapuch\Time;
use Nette\Mail;

/**
 * Subscriptions sending to an email
 */
final class EmailSubscription implements Subscription {
	private const SENDER = 'Remembrall <remembrall@remembrall.org>';
	private const TEMPLATES = __DIR__ . '/../../Messages/Subscription',
		SUBJECT = self::TEMPLATES . '/subject.xsl',
		CONTENT = self::TEMPLATES . '/content.xsl',
		SCHEMA = self::TEMPLATES . '/constraint.xsd';
	private $origin;
	private $mailer;
	private $recipients;

	public function __construct(
		Subscription $origin,
		Mail\IMailer $mailer,
		array $recipients
	) {
		$this->origin = $origin;
		$this->mailer = $mailer;
		$this->recipients = $recipients;
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
			array_reduce(
				$this->recipients,
				function(Mail\Message $message, string $recipient): Mail\Message {
					return $message->addBcc($recipient);
				},
				(new Mail\Message())
					->setFrom(self::SENDER)
					->setSubject(
						(new Output\XsltTemplate(
							self::SUBJECT,
							new Output\ValidXml(
								$this->print(new Output\Xml([], 'part')),
								self::SCHEMA
							)
						))->render()
					)
					->setHtmlBody(
						(new Output\XsltTemplate(
							self::CONTENT,
							new Output\ValidXml(
								$this->print(new Output\Xml([], 'part')),
								self::SCHEMA
							)
						))->render()
					)
			)
		);
	}

	public function print(Output\Format $format): Output\Format {
		return $this->origin->print($format);
	}
}