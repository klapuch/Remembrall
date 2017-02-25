<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Subscribing;

use Klapuch\Dataset;
use Klapuch\Uri;
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class CollectiveParts extends TestCase\Database {
	public function testAddingBrandNewOne() {
		(new Subscribing\CollectiveParts(
			$this->database
		))->add(
			new Subscribing\FakePart('google content', null, 'google snap'),
			new Uri\FakeUri('www.google.com'),
			'//p'
		);
		$statement = $this->database->prepare('SELECT * FROM parts');
		$statement->execute();
		$parts = $statement->fetchAll();
		Assert::count(1, $parts);
		Assert::same('www.google.com', $parts[0]['page_url']);
		Assert::same('google content', $parts[0]['content']);
		Assert::same('google snap', $parts[0]['snapshot']);
		Assert::same('//p', $parts[0]['expression']);
	}

	public function testAddingToOthers() {
		$parts = new Subscribing\CollectiveParts($this->database);
		$parts->add(
			new Subscribing\FakePart('google content', null, 'google snap'),
			new Uri\FakeUri('www.google.com'),
			'//google'
		);
		$parts->add(
			new Subscribing\FakePart('facedown content', null, 'facedown snap'),
			new Uri\FakeUri('www.facedown.cz'),
			'//facedown'
		);
		$statement = $this->database->prepare('SELECT * FROM parts');
		$statement->execute();
		$parts = $statement->fetchAll();
		Assert::count(2, $parts);
		Assert::same('www.google.com', $parts[0]['page_url']);
		Assert::same('google content', $parts[0]['content']);
		Assert::same('google snap', $parts[0]['snapshot']);
		Assert::same('//google', $parts[0]['expression']);
		Assert::same('www.facedown.cz', $parts[1]['page_url']);
		Assert::same('facedown content', $parts[1]['content']);
		Assert::same('facedown snap', $parts[1]['snapshot']);
		Assert::same('//facedown', $parts[1]['expression']);
	}

	public function testAddingWithRecordedVisitation() {
		$this->truncate(['part_visits']);
		(new Subscribing\CollectiveParts(
			$this->database
		))->add(
			new Subscribing\FakePart('<p>Content</p>', null, ''),
			new Uri\FakeUri('www.google.com'),
			'//p'
		);
		$statement = $this->database->prepare("SELECT * FROM part_visits");
		$statement->execute();
		Assert::count(1, $statement->fetchAll());
	}

	public function testUpdatingDuplication() {
		$oldPart = new Subscribing\FakePart('Content', null, 'OLD_SNAP');
		$newPart = new Subscribing\FakePart('NEW_CONTENT', null, 'NEW_SNAP');
		$parts = new Subscribing\CollectiveParts($this->database);
		$parts->add($oldPart, new Uri\FakeUri('www.google.com'), '//p');
		$parts->add($newPart, new Uri\FakeUri('www.google.com'), '//p');
		$statement = $this->database->prepare('SELECT * FROM parts');
		$statement->execute();
		$parts = $statement->fetchAll();
		Assert::count(1, $parts);
		Assert::same('NEW_CONTENT', $parts[0]['content']);
		Assert::same('NEW_SNAP', $parts[0]['snapshot']);
	}

	public function testUpdatingDuplicationWithAllRecordedVisitation() {
		$this->truncate(['part_visits']);
		$oldPart = new Subscribing\FakePart('Content', null, 'OLD_SNAP');
		$newPart = new Subscribing\FakePart('NEW_CONTENT', null, 'NEW_SNAP');
		$parts = new Subscribing\CollectiveParts($this->database);
		$parts->add($oldPart, new Uri\FakeUri('www.google.com'), '//p');
		$parts->add($newPart, new Uri\FakeUri('www.google.com'), '//p');
		$statement = $this->database->prepare('SELECT * FROM part_visits');
		$statement->execute();
		Assert::count(2, $statement->fetchAll());
	}

	public function testIterating() {
		$this->database->exec(
			"INSERT INTO parts (page_url, expression, content, snapshot) VALUES
			('www.google.com', '//a', 'a', ''),
			('www.seznam.cz', '//b', 'b', ''),
			('www.facedown.cz', '//c', 'c', '')"
		);
		$this->database->exec(
			"INSERT INTO subscriptions (user_id, part_id, interval, last_update, snapshot) VALUES
			(1, 1, 'PT1M', NOW(), md5(random()::text)),
			(2, 1, 'PT1M', NOW(), md5(random()::text)),
			(3, 2, 'PT1M', NOW(), md5(random()::text)),
			(4, 4, 'PT1M', NOW(), md5(random()::text))"
		);
		$parts = (new Subscribing\CollectiveParts(
			$this->database
		))->iterate(new Dataset\FakeSelection(''));
		$part = $parts->current();
		Assert::same('a', $part->content());
		$parts->next();
		$part = $parts->current();
		Assert::same('b', $part->content());
		$parts->next();
		$part = $parts->current();
		Assert::same('c', $part->content());
		$parts->next();
		Assert::null($parts->current());
	}

	public function testCounting() {
		$this->database->exec(
			"INSERT INTO parts (page_url, expression, content, snapshot) VALUES
			('www.google.com', '//a', 'a', ''),
			('www.seznam.cz', '//b', 'b', ''),
			('www.facedown.cz', '//c', 'c', '')"
		);
		$parts = new Subscribing\CollectiveParts($this->database);
		Assert::same(3, $parts->count());
	}

	public function testIteratingPrinting() {
		$parts = (new Subscribing\CollectiveParts(
			$this->database
		))->iterate(new Dataset\FakeSelection(''));
		Assert::null($parts->current());
	}

	protected function prepareDatabase() {
		$this->purge(['parts', 'subscriptions']);
	}
}

(new CollectiveParts)->run();