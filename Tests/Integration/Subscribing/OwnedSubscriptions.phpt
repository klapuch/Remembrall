<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Subscribing;

use Klapuch\Access;
use Klapuch\Dataset;
use Klapuch\Output;
use Klapuch\Time;
use Klapuch\Uri;
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class OwnedSubscriptions extends \Tester\TestCase {
	use TestCase\Database;

	public function testSubscribingBrandNewOne() {
		$this->database->exec(
			"INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES
			(1, 'www.google.com', '//google', 'google content', 'google snap')"
		);
		(new Subscribing\OwnedSubscriptions(
			new Access\FakeUser(666),
			$this->database
		))->subscribe(
			new Uri\FakeUri('www.google.com'),
			'//google',
			'xpath',
			new Time\FakeInterval(null, null, 'PT120S')
		);
		$statement = $this->database->prepare('SELECT * FROM subscriptions');
		$statement->execute();
		$subscriptions = $statement->fetchAll();
		Assert::count(1, $subscriptions);
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
			new Access\FakeUser(666),
			$this->database
		);
		$subscribe = function(string $language = 'xpath') use ($subscriptions) {
			$subscriptions->subscribe(
				new Uri\FakeUri('www.google.com'),
				'//google',
				$language,
				new Time\FakeInterval(null, null, 'PT120S')
			);
		};
		Assert::noError(function() use ($subscribe) {
			$subscribe();
			$subscribe('css');
		});
		$ex = Assert::exception(
			$subscribe,
			\Remembrall\Exception\DuplicateException::class,
			'"//google" expression on "www.google.com" page is already subscribed by you'
		);
		Assert::type(\Throwable::class, $ex->getPrevious());
	}

	public function testIteratingOwned() {
		$this->database->exec(
			"INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES
			(1, 'https://www.google.com', '//a', 'a', ''),
			(2, 'http://www.facedown.cz', '//b', 'b', ''),
			(3, 'http://www.facedown.cz', '//c', 'c', ''),
			(4, 'https://www.google.com', '//d', 'd', '')"
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
			"INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES
			(5, 'www.google.com', '//google', 'google content', 'google snap')"
		);
		$subscriptions = (new Subscribing\OwnedSubscriptions(
			new Access\FakeUser(1),
			$this->database
		))->all(new Dataset\FakeSelection('', []));
		$subscription = $subscriptions->current();
		Assert::contains(
			'1993-01-01',
			$subscription->print(new Output\FakeFormat(''))->serialization()
		);
		$subscriptions->next();
		$subscription = $subscriptions->current();
		Assert::contains(
			'1997-01-01',
			$subscription->print(new Output\FakeFormat(''))->serialization()
		);
		$subscriptions->next();
		$subscription = $subscriptions->current();
		Assert::contains(
			'1996-01-01',
			$subscription->print(new Output\FakeFormat(''))->serialization()
		);
		$subscriptions->next();
		Assert::null($subscriptions->current());
	}

	public function testIteratingWithoutVisits() {
		$this->database->exec(
			"INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES
			(1, 'https://www.google.com', '//a', 'a', '')"
		);
		$this->database->exec(
			"INSERT INTO subscriptions (part_id, user_id, interval, last_update, snapshot) VALUES
			(1, 1, 'PT1M', '1993-01-01', '')"
		);
		$this->truncate(['part_visits']);
		$subscriptions = (new Subscribing\OwnedSubscriptions(
			new Access\FakeUser(1),
			$this->database
		))->all(new Dataset\FakeSelection('', []));
		$subscription = $subscriptions->current();
		Assert::notSame(null, $subscription);
	}

	public function testEmptyIterating() {
		$subscriptions = (new Subscribing\OwnedSubscriptions(
			new Access\FakeUser(1),
			$this->database
		))->all(new Dataset\FakeSelection('', []));
		Assert::null($subscriptions->current());
	}
}

(new OwnedSubscriptions)->run();