<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;
use Nette\Security;

require __DIR__ . '/../../bootstrap.php';

final class ExpiredParts extends TestCase\Database {
    public function testIteratingExpiredPartsOnConcretePage() {
        $parts = (new Subscribing\ExpiredParts(
            new Subscribing\FakeParts(),
			new Subscribing\FakePage('a'),
			$this->database
        ))->iterate();
        Assert::count(2, $parts);
        Assert::same('//a', (string)$parts[0]->expression());
        Assert::same('//d', (string)$parts[1]->expression());
    }

	/**
	 * @throws \Remembrall\Exception\ExistenceException This part has not expired yet
	 */
	public function testReplacingNonExpiredPart() {
		(new Subscribing\ExpiredParts(
			new Subscribing\FakeParts(),
			new Subscribing\FakePage('a'),
			$this->database
		))->replace(
			new Subscribing\FakePart(
				'c',
				new Subscribing\FakePage('google.com'),
				false, // non-expired
				new Subscribing\FakeExpression('//p')
			),
			new Subscribing\FakePart()
		);
	}

	public function testReplacingExpiredPartWithNoError() {
		(new Subscribing\ExpiredParts(
			new Subscribing\FakeParts(),
			new Subscribing\FakePage('a'),
			$this->database
		))->replace(
			new Subscribing\FakePart(
				'c',
				new Subscribing\FakePage('google.com'),
				true, // expired
				new Subscribing\FakeExpression('//p')
			),
			new Subscribing\FakePart()
		);
		Assert::true(true);
	}

	protected function prepareDatabase() {
        $this->database->query('TRUNCATE part_visits');
		$this->database->query(
			'INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, NOW() - INTERVAL 2 DAY), (3, NOW() - INTERVAL 3 MINUTE)'
		);
		$this->database->query('TRUNCATE parts');
		$this->database->query(
			'INSERT INTO parts (ID, page_id, expression, content, `interval`, subscriber_id) VALUES
			(1, 1, "//a", "a", 10, 1)'
		);
		$this->database->query(
			'INSERT INTO parts (ID, page_id, expression, content, `interval`, subscriber_id) VALUES
			(2, 2, "//b", "b", 10, 2)'
		);
		$this->database->query(
			'INSERT INTO parts (ID, page_id, expression, content, `interval`, subscriber_id) VALUES
			(3, 1, "//c", "c", 10, 1)'
		);
		$this->database->query(
			'INSERT INTO parts (ID, page_id, expression, content, `interval`, subscriber_id) VALUES
			(4, 1, "//d", "d", 10, 1)'
		);
		$this->database->query('TRUNCATE pages');
		$this->database->query(
			'INSERT INTO pages (ID, url, content) VALUES
			(1, "a", "xx"), (2, "b", "zz"), (3, "c", "yy")'
		);
    }
}

(new ExpiredParts)->run();
