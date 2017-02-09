<?php
declare(strict_types = 1);
namespace Remembrall\Page\Cron;

use Klapuch\Output;
use Nette\Mail;
use Remembrall\Page;
use Remembrall\Model\{
	Subscribing, Misc
};

final class DefaultPage extends Page\BasePage {
	public function render(array $parameters): Output\Format {
		try {
			$parts = new Subscribing\HarnessedParts(
				new Subscribing\UnreliableParts(
					new Subscribing\CollectiveParts($this->database),
					$this->database
				),
				new Misc\LoggingCallback($this->logs)
			);
			/** @var \Remembrall\Model\Subscribing\Part $part */
			foreach($parts as $part) {
				try {
					$part->refresh();
				} catch(\Throwable $ex) {
					$this->log($ex);
				}
			}
			$subscriptions = new Subscribing\HarnessedSubscriptions(
				new Subscribing\ChangedSubscriptions(
					new Subscribing\FakeSubscriptions(),
					new Mail\SendmailMailer(),
					$this->database
				),
				new Misc\LoggingCallback($this->logs)
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