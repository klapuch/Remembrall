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
			'INSERT INTO parts (page_id, expression, content, `interval`, subscriber_id) VALUES
			(1, "//p", "a", "PT1M", 1),
			(2, "//h1", "b", "PT1M", 1),
			(1, "//h2", "c", "PT2M", 2),
			(1, "//h2", "d", "PT2M", 1)'
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
		Assert::same(1, $reports[0]->id());
		Assert::same($owner, $reports[0]->recipient());
		Assert::same(2, $reports[1]->id());
		Assert::same($owner, $reports[1]->recipient());
		Assert::same(4, $reports[2]->id());
		Assert::same($owner, $reports[2]->recipient());
	}

	public function testArchiving() {
		$this->database->query(
			'INSERT INTO parts (ID, page_id, expression, subscriber_id) VALUES 
			(5, 1, "//h1", 1)'
		);
		(new Subscribing\OwnedReports(
			new Access\FakeSubscriber(1),
			$this->database
		))->archive(
			new Subscribing\FakePart(
				null,
				new Subscribing\FakePage('www.google.com'),
				false,
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
