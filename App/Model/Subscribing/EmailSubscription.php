<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Output;
use Klapuch\Time;
use Nette\Mail;
use Remembrall\Model\Web;

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
	private $recipient;
	private $part;

	public function __construct(
		Subscription $origin,
		Mail\IMailer $mailer,
		string $recipient,
		Web\Part $part
	) {
		$this->origin = $origin;
		$this->mailer = $mailer;
		$this->recipient = $recipient;
		$this->part = $part;
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
						$this->part->print(new Output\Xml([], 'part')),
						self::SCHEMA
					)
				))->render()
			)
			->setHtmlBody(
				(new Output\XsltTemplate(
					self::CONTENT,
					new Output\ValidXml(
						$this->part->print(new Output\Xml([], 'part')),
						self::SCHEMA
					)
				))->render()
			)
		);
	}

	public function print(Output\Format $format): Output\Format {
		return $this->origin->print($format);
	}
}