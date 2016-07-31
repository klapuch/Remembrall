<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Access;

use Dibi;
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
			("foo@bar.cz", "password"),
			("someone@else.cz", "password")'
		);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content, content_hash) VALUES
			("www.google.com", "//h1", "content1", "content1HASH"),
			("www.google.com", "//h2", "content2", "content2HASH"),
			("www.google.com", "//h3", "content3", "content3HASH")'
		);
		$this->purge(['part_visits']);
		$this->database->query(
			'INSERT INTO part_visits (part_id, visited_at) VALUES 
			(1, "2000-01-01 01:01"),
			(1, NOW() - INTERVAL "35 MINUTE")'
		);
		$this->database->query(
			'INSERT INTO subscriptions (part_id, subscriber_id, interval, hash) VALUES
			(1, 1, "PT30M","A"),
			(1, 4, "PT30M","A"),
			(2, 2, "PT30M","A"),
			(1, 3, "PT40M","A")'
		);
		Assert::equal(
			[
				new Access\ConstantSubscriber(1, 'facedown@gmail.com'),
				new Access\ConstantSubscriber(4, 'someone@else.cz'),
			],
			(new Access\OutdatedSubscribers(
				new Access\FakeSubscribers(),
				'www.google.com',
				'//h1',
				$this->database
			))->iterate()
		);
		Assert::equal(
			[
				new Dibi\Row(
					['hash' => 'A', 'part_id' => 2, 'subscriber_id' => 2]
				),
				new Dibi\Row(
					['hash' => 'A', 'part_id' => 1, 'subscriber_id' => 3]
				),
				new Dibi\Row(
					['hash' => 'content1HASH', 'part_id' => 1, 'subscriber_id' => 1]
				),
				new Dibi\Row(
					['hash' => 'content1HASH', 'part_id' => 1, 'subscriber_id' => 4]
				),
			],
			$this->database->fetchAll(
				'SELECT hash, part_id, subscriber_id
				FROM subscriptions'
			)
		);
	}

	protected function prepareDatabase() {
		$this->purge(['subscriptions', 'parts', 'part_visits', 'subscribers']);
	}
}

(new OutdatedSubscribers())->run();
