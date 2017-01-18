<?php
declare(strict_types = 1);
namespace Remembrall\Page\Cron;

use Nette\Mail;
use Remembrall\Model\Subscribing;
use Remembrall\Page;

final class DefaultPage extends Page\BasePage {
	public function render(): \SimpleXMLElement {
		try {
			$parts = new Subscribing\LoggedParts(
				new Subscribing\UnreliableParts(
					new Subscribing\CollectiveParts($this->database),
					$this->database
				),
				$this->logs
			);
			/** @var \Remembrall\Model\Subscribing\Part $part */
			foreach($parts as $part) {
				try {
					$part->refresh();
				} catch(\Throwable $ex) {
					$this->log($ex);
				}
			}
			$subscriptions = new Subscribing\LoggedSubscriptions(
				new Subscribing\ChangedSubscriptions(
					new Subscribing\FakeSubscriptions(),
					new Mail\SendmailMailer(),
					$this->database
				),
				$this->logs
			);
			/** @var \Remembrall\Model\Subscribing\Subscription $subscription */
			foreach($subscriptions as $subscription) {
				try {
					$subscription->notify();
				} catch(\Throwable $ex) {
					$this->log($ex);
				}
			}
			exit('OK');
		} catch(\Throwable $ex) {
			throw $ex;
		}
	}
}