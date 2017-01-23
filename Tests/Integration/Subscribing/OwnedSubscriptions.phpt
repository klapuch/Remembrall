<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Subscribing;

use Klapuch\{
	Output, Storage\UniqueConstraint, Time, Uri
};
use Remembrall\Exception\DuplicateException;
use Remembrall\Model\{
	Access, Subscribing
};
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class OwnedSubscriptions extends TestCase\Database {
	public function testSubscribingBrandNewOne() {
		$this->database->exec(
			"INSERT INTO parts (page_url, expression, content, snapshot) VALUES
			('www.google.com', '//google', 'google content', 'google snap')"
		);
		(new Subscribing\OwnedSubscriptions(
			new Access\FakeSubscriber(666),
			$this->database
		))->subscribe(
			new Uri\FakeUri('www.google.com'),
			'//google',
			new Time\FakeInterval(null, null, 'PT120S')
		);
		$statement = $this->database->prepare('SELECT * FROM subscriptions');
		$statement->execute();
		$subscriptions = $statement->fetchAll();
		Assert::count(1, $subscriptions);
		Assert::same(1, $subscriptions[0]['id']);
		Assert::same(666, $subscriptions[0]['user_id']);
		Assert::same('PT120S', $subscriptions[0]['interval']);
		Assert::same('google snap', $subscriptions[0]['snapshot']);
	}

	public function testThrowingOnDuplication() {
		$this->database->exec(
			"INSERT INTO parts (page_url, expression, content, snapshot) VALUES
			('www.google.com', '//google', 'google content', 'google snap')"
		);
		$subscriptions = new Subscribing\OwnedSubscriptions(
			new Access\FakeSubscriber(666),
			$this->database
		);
		$subscribe = function() use($subscriptions) {
			$subscriptions->subscribe(
				new Uri\FakeUri('www.google.com'),
				'//google',
				new Time\FakeInterval(null, null, 'PT120S')
			);
		}; 
		$subscribe();
		$ex = Assert::exception(
			$subscribe,
			DuplicateException::class,
			'"//google" expression on "www.google.com" page is already subscribed by you'
		);
		Assert::type(\Throwable::class, $ex->getPrevious());
	}

	public function testPrinting() {
		$this->database->exec(
			"INSERT INTO parts (page_url, expression, content, snapshot) VALUES
			('https://www.google.com', '//a', 'a', ''),
			('http://www.facedown.cz', '//b', 'b', ''),
			('http://www.facedown.cz', '//c', 'c', ''),
			('https://www.google.com', '//d', 'd', '')"
		);
		$this->database->exec(
			"INSERT INTO subscriptions (part_id, user_id, interval, last_update, snapshot) VALUES
			(1, 1, 'PT1M', '1993-01-01', ''),
			(2, 2, 'PT2M', '1994-01-01', ''),
			(3, 1, 'PT3M', '1996-01-01', ''),
			(4, 1, 'PT4M', '1997-01-01', '')"
		);
		$this->truncate(['part_visits']);
		$this->database->exec(
			"INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, '2000-01-01 01:01:01'),
			(1, '2008-01-01 01:01:01'),
			(2, '2001-01-01 01:01:01'),
			(3, '2002-01-01 01:01:01'),
			(4, '2003-01-01 01:01:01')"
		);
		$this->database->exec(
			"INSERT INTO parts (page_url, expression, content, snapshot) VALUES
			('www.google.com', '//google', 'google content', 'google snap')"
		);
		$subscriptions = (new Subscribing\OwnedSubscriptions(
			new Access\FakeSubscriber(1, 'idk@email.cz'),
			$this->database
		))->print(new Output\FakeFormat(''));
		Assert::count(3, $subscriptions);
		Assert::contains('1993-01-01', $subscriptions[0]->serialization());
		Assert::contains('1997-01-01', $subscriptions[1]->serialization());
		Assert::contains('1996-01-01', $subscriptions[2]->serialization());
	}

	public function testEmptyPrinting() {
		Assert::same(
			[],
			(new Subscribing\OwnedSubscriptions(
				new Access\FakeSubscriber(1),
				$this->database
			))->print(new Output\FakeFormat(''))
		);
	}

	public function testEmptyIterating() {
		$subscriptions = (new Subscribing\OwnedSubscriptions(
			new Access\FakeSubscriber(1),
			$this->database
		))->getIterator();
		Assert::null($subscriptions->current());
	}

	public function testIteratingOwned() {
		$this->database->exec(
			"INSERT INTO subscriptions (part_id, user_id, interval, last_update, snapshot) VALUES
			(1, 4, 'PT1M', NOW(), ''),
			(2, 2, 'PT2M', NOW(), ''),
			(3, 1, 'PT3M', NOW(), ''),
			(4, 1, 'PT4M', NOW(), '')"
		);
		$subscriptions = (new Subscribing\OwnedSubscriptions(
			new Access\FakeSubscriber(1),
			$this->database
		))->getIterator();
		$subscription = $subscriptions->current();
		Assert::equal(
			new Subscribing\StoredSubscription(3, $this->database),
			$subscription
		);
		$subscriptions->next();
		$subscription = $subscriptions->current();
		Assert::equal(
			new Subscribing\StoredSubscription(4, $this->database),
			$subscription
		);
		$subscriptions->next();
		Assert::null($subscriptions->current());
	}

	protected function prepareDatabase() {
		$this->truncate(['parts', 'subscriptions']);
		$this->restartSequence(['parts', 'subscriptions']);
	}
}

(new OwnedSubscriptions)->run();