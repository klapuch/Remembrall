<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use Dibi;
use Remembrall\Model\{
	Access, Subscribing
};
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class OwnedReports extends TestCase\Database {
	public function testIteratingOwnerReports() {
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content) VALUES
			(1, "//p", "a"),
			(2, "//h1", "b"),
			(1, "//h2", "c"),
			(1, "//h3", "d")'
		);
		$this->database->query(
			'INSERT INTO subscribed_parts (part_id, subscriber_id, `interval`) VALUES
			(1, 1, "PT1M"), (2, 1, "PT1M"), (3, 2, "PT2M"), (4, 1, "PT2M")'
		);
		$this->database->query(
			'INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, NOW()), (2, NOW()), (3, NOW()), (4, NOW())'
		);
		$this->database->query(
			'INSERT INTO reports (part_id, sent_at) VALUES 
			(1, NOW()), (2, NOW()), (3, NOW()), (4, NOW())'
		);
		$reports = (new Subscribing\OwnedReports(
			$owner = new Access\FakeSubscriber(1),
			$this->database
		))->iterate();
		Assert::count(3, $reports);
		Assert::same('//h3', (string)$reports[0]->content()->expression());
		Assert::same($owner, $reports[0]->recipient());
		Assert::same('//h1', (string)$reports[1]->content()->expression());
		Assert::same($owner, $reports[1]->recipient());
		Assert::same('//p', (string)$reports[2]->content()->expression());
		Assert::same($owner, $reports[2]->recipient());
	}

	public function testArchiving() {
		$this->database->query(
			'INSERT INTO parts (ID, page_id, expression) VALUES 
			(5, 1, "//h1")'
		);
		$this->database->query(
			'INSERT INTO subscribed_parts (part_id, subscriber_id, `interval`) VALUES
			(5, 1, "PT1M")'
		);
		(new Subscribing\OwnedReports(
			new Access\FakeSubscriber(1),
			$this->database
		))->archive(
			new Subscribing\FakePart(
				new Subscribing\FakePage('www.google.com'),
				new Subscribing\FakeExpression('//h1')
			)
		);
		$rows = $this->database->fetchAll('SELECT part_id FROM reports');
		Assert::count(1, $rows);
		Assert::same(5, $rows[0]['part_id']);
	}

	protected function prepareDatabase() {
		$this->database->query('TRUNCATE parts');
		$this->database->query('TRUNCATE reports');
		$this->database->query('TRUNCATE part_visits');
		$this->database->query('TRUNCATE pages');
		$this->database->query('TRUNCATE subscribers');
		$this->database->query('TRUNCATE subscribed_parts');
		$this->database->query(
			'INSERT INTO pages (ID, url, content) VALUES
			(1, "www.google.com", "<p>google</p>"),
			(2, "www.facedown.cz", "<p>facedown</p>")'
		);
		$this->database->query(
			'INSERT INTO subscribers (ID, email, `password`) VALUES
			(1, "google@google.com", "googlePassword"),
			(2, "facedown@facedown.com", "facedownPassword")'
		);
	}
}

(new OwnedReports)->run();
