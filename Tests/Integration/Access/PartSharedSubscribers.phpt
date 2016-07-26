<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Access;

use Remembrall\Model\{
	Access, Subscribing
};
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class PartSharedSubscribers extends TestCase\Database {
	public function testIteratingSharedParts() {
		$this->database->query(
			'INSERT INTO pages (url, content) VALUES
			("www.google.com", "google"),
			("www.facedown.cz", "seznam")'
		);
		$this->database->query(
			'INSERT INTO subscribers (id, email, password) VALUES
			(1, "facedown@gmail.com", "password"),
			(2, "facedown@facedown.cz", "password"),
			(3, "foo@bar.cz", "password")'
		);
		$this->database->query(
			'INSERT INTO parts (id, page_url, expression, content) VALUES
			(1, "www.google.com", "//h1", "content"),
			(2, "www.facedown.cz", "//h1", "content")'
		);
		$this->database->query(
			'INSERT INTO subscriptions (id, part_id, subscriber_id, interval) VALUES
			(1, 1, 1, "PT1M"),
			(2, 1, 2, "PT1M"),
			(3, 2, 2, "PT1M"),
			(4, 2, 3, "PT1M")'
		);
		Assert::equal(
			[
				new Access\ConstantSubscriber(1, 'facedown@gmail.com'),
				new Access\ConstantSubscriber(2, 'facedown@facedown.cz'),
			],
			(new Access\PartSharedSubscribers(
				new Access\FakeSubscribers(),
				'www.google.com',
				'//h1',
				$this->database
			))->iterate()
		);
	}

	protected function prepareDatabase() {
		$this->truncate(['pages', 'subscriptions', 'parts', 'subscribers']);
		$this->restartSequence(['subscriptions', 'parts', 'subscribers']);
	}
}

(new PartSharedSubscribers())->run();
