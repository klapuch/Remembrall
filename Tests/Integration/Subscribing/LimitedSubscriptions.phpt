<?php
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Subscribing;

use Klapuch\{
	Time, Uri
};
use Remembrall\Model\{
	Access, Subscribing
};
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class LimitedSubscriptions extends TestCase\Database {
	public function testSubscribingInLimit() {
		Assert::noError(
			function() {
				(new Subscribing\LimitedSubscriptions(
					new Subscribing\FakeSubscriptions(),
					new Access\FakeSubscriber(666),
					$this->database
				))->subscribe(
					new Uri\FakeUri('url'),
					'//p',
					new Time\FakeInterval()
				);
			}
		);
	}

	/**
	 * @throws \OverflowException You have reached the limit of 5 subscribed parts
	 */
	public function testSubscribingOverLimit() {
		$this->database->query(
			"INSERT INTO parts (page_url, expression, content, snapshot) VALUES
			('www.google.com', '//a', 'a', ''),
			('www.facedown.cz', '//b', 'b', ''),
			('www.facedown.cz', '//c', 'c', ''),
			('www.google.com', '//d', 'd', ''),
			('www.facedown.cz', '//d', 'd', '')"
		);
		$this->database->query(
			"INSERT INTO subscriptions (part_id, subscriber_id, interval, last_update, snapshot) VALUES
			(1, 666, 'PT1M', NOW(), ''),
			(2, 666, 'PT2M', NOW(), ''),
			(3, 666, 'PT3M', NOW(), ''),
			(4, 666, 'PT4M', NOW(), ''),
			(5, 666, 'PT5M', NOW(), '')"
		);
		(new Subscribing\LimitedSubscriptions(
			new Subscribing\FakeSubscriptions(),
			new Access\FakeSubscriber(666),
			$this->database
		))->subscribe(new Uri\FakeUri('url'), '//p', new Time\FakeInterval());
	}

	protected function prepareDatabase() {
		$this->purge(['parts', 'subscriptions']);
	}
}

(new LimitedSubscriptions)->run();
