<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Nette\Mail;
use Remembrall\Model\Subscribing;

final class CronPage extends BasePage {
	/** @var Mail\IMailer @inject */
	public $mailer;

	public function actionDefault() {
		try {
			$parts = (new Subscribing\LoggedParts(
				new Subscribing\UnreliableParts(
					new Subscribing\CollectiveParts($this->database),
					$this->database
				),
				$this->logger
			))->iterate();
			/** @var Subscribing\Part $part */
			foreach($parts as $part)
				$part->refresh();
			$subscriptions = (new Subscribing\LoggedSubscriptions(
				new Subscribing\ChangedSubscriptions(
					new Subscribing\OwnedSubscriptions(
						$this->subscriber,
						$this->database
					),
					$this->mailer,
					$this->database
				),
				$this->logger
			))->iterate();
			/** @var Subscribing\Subscription $subscription */
			foreach($subscriptions as $subscription)
				$subscription->notify();
			echo 'OK';
		} catch(\Throwable $ex) {
			echo $ex->getMessage();
		}
	}
}