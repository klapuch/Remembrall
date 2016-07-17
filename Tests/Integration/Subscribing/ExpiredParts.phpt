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
    public function testIteratingExpiredParts() {
        $parts = (new Subscribing\ExpiredParts(
            new Subscribing\FakeParts(),
			$this->database
        ))->iterate();
        Assert::count(2, $parts);
        Assert::same('//a', (string)$parts[0]->print()['expression']);
        Assert::same('//d', (string)$parts[1]->print()['expression']);
    }

	protected function prepareDatabase() {
        $this->database->query('TRUNCATE part_visits');
		$this->database->query(
			'INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, NOW() - INTERVAL 2 DAY),
			(2, NOW()),
			(3, NOW() - INTERVAL 3 MINUTE),
			(1, NOW() - INTERVAL 4 DAY)'
		);
		$this->database->query('TRUNCATE parts');
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.google.com", "//a", "a")'
		);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.facedown.cz", "//b", "b")'
		);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.google.com", "//c", "c")'
		);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.facedown.cz", "//d", "d")'
		);
		$this->database->query('TRUNCATE subscribed_parts');
		$this->database->query(
			'INSERT INTO subscribed_parts (part_id, subscriber_id, `interval`) VALUES
			(1, 1, "PT10M"), (2, 2, "PT10M"), (3, 1, "PT10M"), (4, 1, "PT10M")'
		);
		$this->database->query('TRUNCATE pages');
		$this->database->query(
			'INSERT INTO pages (url, content) VALUES
			("www.google.com", "google"), ("www.facedown.cz", "facedown"), ("www.foo.cz", "foo")'
		);
    }
}

(new ExpiredParts)->run();
