<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Access;

use Remembrall\Model\Access;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class OutdatedSubscribers extends TestCase\Database {
	public function testIteratingOutdatedSubscribers() {
		$this->database->query(
			'INSERT INTO subscribers (email, password) VALUES
			("facedown@gmail.com", "password"),
			("facedown@facedown.cz", "password"),
			("foo@bar.cz", "password")'
		);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content, content_hash) VALUES
			("www.google.com", "//h1", "content", MD5("content")),
			("www.google.com", "//h2", "content", MD5("content")),
			("www.google.com", "//h3", "content", MD5("content"))'
		);
		$this->database->query(
			'INSERT INTO part_visits (part_id, visited_at) VALUES 
			(1, "2000-01-01 01:01"),
			(1, NOW() - INTERVAL "35 MINUTE")'
		);
		$this->database->query(
			'INSERT INTO subscriptions (part_id, subscriber_id, interval) VALUES
			(1, 1, "PT30M"),
			(2, 2, "PT30M"),
			(1, 3, "PT40M")'
		);
		Assert::equal(
			[
				new Access\ConstantSubscriber(1, 'facedown@gmail.com'),
			],
			(new Access\OutdatedSubscribers(
				new Access\FakeSubscribers(),
				'www.google.com',
				'//h1',
				$this->database
			))->iterate()
		);
	}

	protected function prepareDatabase() {
		$this->purge(['subscriptions', 'parts', 'part_visits', 'subscribers']);
	}
}

(new OutdatedSubscribers())->run();
