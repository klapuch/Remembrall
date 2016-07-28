<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use Dibi;
use Remembrall\Model\{
	Subscribing, Access
};
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class LimitedSubscriptions extends TestCase\Database {
	public function testSubscribingWithoutLimit() {
		Assert::noError(function() {
			(new Subscribing\LimitedSubscriptions(
				$this->database,
				new Access\FakeSubscriber(666),
				new Subscribing\FakeSubscriptions()
			))->subscribe(
				'url',
				'//p',
				new Subscribing\FakeInterval()
			);
		});
	}

	/**
	 * @throws \OverflowException You have reached limit of 5 subscribed parts
	 */
	public function testSubscribingOverLimit() {
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.facedown.cz", "//d", "d")'
		);
		$this->database->query(
			'INSERT INTO subscriptions (part_id, subscriber_id, interval) VALUES
			(5, 666, "PT5M")'
		);
		(new Subscribing\LimitedSubscriptions(
			$this->database,
			new Access\FakeSubscriber(666),
			new Subscribing\FakeSubscriptions()
		))->subscribe(
			'url',
			'//p',
			new Subscribing\FakeInterval()
		);
	}

    protected function prepareDatabase() {
    	$this->purge(['parts', 'subscriptions']);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.google.com", "//a", "a"),
			("www.facedown.cz", "//b", "b"),
			("www.facedown.cz", "//c", "c"),
			("www.google.com", "//d", "d")'
		);
		$this->database->query(
			'INSERT INTO subscriptions (part_id, subscriber_id, interval) VALUES
			(1, 666, "PT1M"),
			(2, 666, "PT2M"),
			(3, 666, "PT3M"),
			(4, 666, "PT4M")'
		);
    }
}

(new LimitedSubscriptions)->run();
