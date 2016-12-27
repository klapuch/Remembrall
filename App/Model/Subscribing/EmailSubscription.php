<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Time;
use Nette\Mail;

/**
 * Subscriptions sending to an email
 */
final class EmailSubscription implements Subscription {
	private $origin;
	private $mailer;
	private $message;

	public function __construct(
		Subscription $origin,
		Mail\IMailer $mailer,
		Mail\Message $message
	) {
		$this->origin = $origin;
		$this->mailer = $mailer;
		$this->message = $message;
	}

	public function cancel(): void {
		$this->origin->cancel();
	}

	public function edit(Time\Interval $interval): void {
		$this->origin->edit($interval);
	}

	public function notify(): void {
		$this->origin->notify();
		$this->mailer->send($this->message);
	}
}