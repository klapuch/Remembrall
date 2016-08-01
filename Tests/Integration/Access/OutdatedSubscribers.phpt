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
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.google.com", "//h1", "content1"),
			("www.google.com", "//h2", "content2"),
			("www.google.com", "//h3", "content3")'
		);
		$this->database->query(
			'INSERT INTO subscriptions (part_id, subscriber_id, interval, last_update) VALUES
			(1, 1, "PT30M", NOW() - INTERVAL "50 MINUTE"),
			(1, 4, "PT30M", NOW() - INTERVAL "31 MINUTE"),
			(2, 2, "PT30M", NOW()),
			(1, 3, "PT30M", NOW() - INTERVAL "29 MINUTE")'
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
					['part_id' => 2, 'subscriber_id' => 2]
				),
				new Dibi\Row(
					['part_id' => 1, 'subscriber_id' => 3]
				),
				new Dibi\Row(
					['part_id' => 1, 'subscriber_id' => 1]
				),
				new Dibi\Row(
					['part_id' => 1, 'subscriber_id' => 4]
				),
			],
			$this->database->fetchAll(
				'SELECT part_id, subscriber_id
				FROM subscriptions
				WHERE last_update <= NOW()'
			)
		);
	}

	protected function prepareDatabase() {
		$this->purge(['subscriptions', 'parts', 'subscribers']);
	}
}

(new OutdatedSubscribers())->run();
