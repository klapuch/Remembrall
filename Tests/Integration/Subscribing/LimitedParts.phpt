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

final class LimitedParts extends TestCase\Database {
	public function testSubscribingWithoutLimit() {
		Assert::noError(function() {
			(new Subscribing\LimitedParts(
				$this->database,
				new Access\FakeSubscriber(666),
				new Subscribing\FakeParts()
			))->subscribe(
				new Subscribing\FakePart(),
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
			("b", "//d", "d")'
		);
		$this->database->query(
			'INSERT INTO subscribed_parts (part_id, subscriber_id, `interval`) VALUES
			(5, 666, "PT5M")'
		);
		(new Subscribing\LimitedParts(
			$this->database,
			new Access\FakeSubscriber(666),
			new Subscribing\FakeParts()
		))->subscribe(
			new Subscribing\FakePart(),
			new Subscribing\FakeInterval()
		);
	}

    protected function prepareDatabase() {
        $this->database->query('TRUNCATE parts');
		$this->database->query('TRUNCATE subscribed_parts');
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("a", "//a", "a")'
		);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("b", "//b", "b")'
		);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("b", "//c", "c")'
		);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("a", "//d", "d")'
		);
		$this->database->query(
			'INSERT INTO subscribed_parts (part_id, subscriber_id, `interval`) VALUES
			(1, 666, "PT1M"), (2, 666, "PT2M"), (3, 666, "PT3M"), (4, 666, "PT4M")'
		);
    }
}

(new LimitedParts)->run();
