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
			("www.google.com", "google"), ("www.facedown.cz", "seznam")'
		);
		$this->database->query(
			'INSERT INTO subscribers (email, `password`) VALUES
			("facedown@gmail.com", "password"),
			("facedown@facedown.cz", "password"),
			("foo@bar.cz", "password")'
		);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.google.com", "//h1", "content"),
			("www.facedown.cz", "//h1", "content")'
		);
		$this->database->query(
			'INSERT INTO subscribed_parts (part_id, subscriber_id, `interval`) VALUES
			(1, 1, "PT1M"), (1, 2, "PT1M"), (2, 2, "PT1M"), (2, 3, "PT1M")'
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
		$this->database->query('TRUNCATE pages');
		$this->database->query('TRUNCATE subscribed_parts');
		$this->database->query('TRUNCATE parts');
		$this->database->query('TRUNCATE subscribers');
	}
}

(new PartSharedSubscribers())->run();
