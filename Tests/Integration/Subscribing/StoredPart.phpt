<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Subscribing;

use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class StoredPart extends TestCase\Database {
	public function testContent() {
		$this->database->exec(
			"INSERT INTO parts (page_url, expression, content, snapshot) VALUES
			('www.facedown.cz', '//facedown', 'facedown content', 'face snap'),
			('www.google.com', '//google', 'google content', 'google snap')"
		);
		Assert::same(
			'facedown content',
			(new Subscribing\StoredPart(
				new Subscribing\FakePart(),
				1,
				$this->database
			))->content()
		);
	}

	public function testSnapshot() {
		$this->database->exec(
			"INSERT INTO parts (page_url, expression, content, snapshot) VALUES
			('www.facedown.cz', '//facedown', 'facedown content', 'face snap'),
			('www.google.com', '//google', 'google content', 'google snap')"
		);
		Assert::same(
			'face snap',
			(new Subscribing\StoredPart(
				new Subscribing\FakePart(),
				1,
				$this->database
			))->snapshot()
		);
	}

	public function testRefreshingToNewContent() {
		$this->database->exec(
			"INSERT INTO parts (page_url, expression, content, snapshot) VALUES
			('www.facedown.cz', '//facedown', 'facedown content', 'face snap'),
			('www.google.com', '//google', 'google content', 'google snap')"
		);
		$id = 1;
		(new Subscribing\StoredPart(
			new Subscribing\FakePart('NEW_CONTENT', null, 'NEW_SNAP'),
			$id,
			$this->database
		))->refresh();
		$statement = $this->database->prepare('SELECT * FROM parts WHERE id = ?');
		$statement->execute([$id]);
		$part = $statement->fetch();
		Assert::same('NEW_CONTENT', $part['content']);
		Assert::same('NEW_SNAP', $part['snapshot']);
	}

	public function testRefreshingWithRecordedVisitation() {
		$this->database->exec(
			"INSERT INTO parts (page_url, expression, content, snapshot) VALUES
			('www.facedown.cz', '//facedown', 'facedown content', 'face snap'),
			('www.google.com', '//google', 'google content', 'google snap')"
		);
		$id = 1;
		$this->purge(['part_visits']);
		(new Subscribing\StoredPart(
			new Subscribing\FakePart('NEW_CONTENT', null, 'NEW_SNAP'),
			$id,
			$this->database
		))->refresh();
		$statement = $this->database->prepare('SELECT * FROM part_visits');
		$statement->execute();
		Assert::count(1, $statement->fetchAll());
	}

	public function testRefreshingWithoutAffectingOthers() {
		$this->database->exec(
			"INSERT INTO parts (page_url, expression, content, snapshot) VALUES
			('www.facedown.cz', '//facedown', 'facedown content', 'face snap'),
			('www.google.com', '//google', 'google content', 'google snap')"
		);
		$id = 1;
		(new Subscribing\StoredPart(
			new Subscribing\FakePart('NEW_CONTENT', null, 'NEW_SNAP'),
			$id,
			$this->database
		))->refresh();
		$statement = $this->database->prepare('SELECT * FROM parts');
		$statement->execute();
		$parts = $statement->fetchAll();
		Assert::count(2, $parts);
		Assert::same(2, $parts[0]['id']);
		Assert::same('google content', $parts[0]['content']);
		Assert::same('google snap', $parts[0]['snapshot']);
		Assert::same($id, $parts[1]['id']);
		Assert::same('NEW_CONTENT', $parts[1]['content']);
		Assert::same('NEW_SNAP', $parts[1]['snapshot']);
	}

	protected function prepareDatabase() {
		$this->purge(['parts']);
	}
}

(new StoredPart)->run();