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
			("google.com", "google"), ("seznam.cz", "seznam")'
		);
		$this->database->query(
			'INSERT INTO subscribers (email) VALUES
			("a"), ("b"), ("c")'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, subscriber_id) VALUES
			(1, "//h1", 1), (1, "//h1", 2), (1, "//h2", 2), (2, "//h1", 3)'
		);
		Assert::equal(
			[
				new Access\ConstantSubscriber(1, 'a'),
				new Access\ConstantSubscriber(2, 'b'),
			],
			(new Access\PartSharedSubscribers(
				new Access\FakeSubscribers(),
				new Subscribing\FakePart(
					new Subscribing\FakePage('google.com'),
					new Subscribing\FakeExpression('//h1')
				),
				$this->database
			))->iterate()
		);
	}

	protected function prepareDatabase() {
		$this->database->query('TRUNCATE pages');
		$this->database->query('TRUNCATE parts');
		$this->database->query('TRUNCATE subscribers');
	}
}

(new PartSharedSubscribers())->run();
