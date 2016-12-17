<?php
declare(strict_types = 1);
namespace Remembrall\Page;

use Nette\Mail;
use Remembrall\Model\Subscribing;

final class CronPage extends BasePage {
	public function renderDefault() {
		try {
			$parts = (new Subscribing\LoggedParts(
				new Subscribing\UnreliableParts(
					new Subscribing\CollectiveParts($this->database),
					$this->database
				),
				$this->logs
			))->iterate();
			/** @var \Remembrall\Model\Subscribing\Part $part */
			foreach($parts as $part) {
				try {
					$part->refresh();
				} catch(\Throwable $ex) {
					$this->log($ex);
				}
			}
			$subscriptions = (new Subscribing\LoggedSubscriptions(
				new Subscribing\ChangedSubscriptions(
					new Subscribing\FakeSubscriptions(),
					new Mail\SendmailMailer(),
					(new Mail\Message())->setFrom('Remembrall <remembrall@remembrall.org>'),
					$this->database
				),
				$this->logs
			))->iterate();
			/** @var \Remembrall\Model\Subscribing\Subscription $subscription */
			foreach($subscriptions as $subscription) {
				try {
					$subscription->notify();
				} catch(\Throwable $ex) {
					$this->log($ex);
				}
			}
			echo 'OK';
		} catch(\Throwable $ex) {
			throw $ex;
		}
	}
}