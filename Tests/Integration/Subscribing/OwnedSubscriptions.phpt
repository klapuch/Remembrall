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
use Remembrall\Misc;
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class OwnedSubscriptions extends \Tester\TestCase {
	use TestCase\Database;

	public function testSubscribingBrandNewOne() {
		(new Misc\SamplePart(
			$this->database,
			[
				'expression' => ['value' => '//google', 'language' => 'xpath'],
				'page_url' => 'www.google.com',
				'snapshot' => 'google snap',
			]
		))->try();
		(new Subscribing\OwnedSubscriptions(
			new Access\FakeUser('666'),
			$this->database
		))->subscribe(
			new Uri\FakeUri('www.google.com'),
			'//google',
			'xpath',
			new Time\FakeInterval(null, null, 'PT120S')
		);
		(new Misc\TableCount($this->database, 'readable_subscriptions()', 1))->assert();
		$subscriptions = $this->database->query('SELECT * FROM readable_subscriptions()')->fetch();
		Assert::same(666, $subscriptions['user_id']);
		Assert::same('PT2M', $subscriptions['interval']);
		Assert::same('google snap', $subscriptions['snapshot']);
	}

	public function testThrowingOnDuplication() {
		(new Misc\SamplePart(
			$this->database,
			[
				'expression' => ['value' => '//google', 'language' => 'xpath'],
				'page_url' => 'www.google.com',
			]
		))->try();
		$subscriptions = new Subscribing\OwnedSubscriptions(
			new Access\FakeUser('666'),
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
			\UnexpectedValueException::class,
			'"//google" expression on "www.google.com" page is already subscribed by you'
		);
		Assert::type(\Throwable::class, $ex->getPrevious());
	}

	public function testIteratingOwned() {
		(new Misc\SamplePart($this->database))->try();
		(new Misc\SamplePart($this->database))->try();
		(new Misc\SamplePart($this->database))->try();
		(new Misc\SamplePart($this->database))->try();
		(new Misc\SampleSubscription($this->database, ['user' => 1, 'part' => 1]))->try();
		(new Misc\SampleSubscription($this->database, ['user' => 2, 'part' => 2]))->try();
		(new Misc\SampleSubscription($this->database, ['user' => 1, 'part' => 3]))->try();
		(new Misc\SampleSubscription($this->database, ['user' => 1, 'part' => 4]))->try();
		$this->truncate(['part_visits']);
		$this->database->exec(
			"INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, '2000-01-01 01:01:01'),
			(1, '2008-01-01 01:01:01'),
			(2, '2001-01-01 01:01:01'),
			(3, '2002-01-01 01:01:01'),
			(4, '2003-01-01 01:01:01')"
		);
		(new Misc\SamplePart($this->database))->try();
		$subscriptions = (new Subscribing\OwnedSubscriptions(
			new Access\FakeUser('1'),
			$this->database
		))->all(new Dataset\FakeSelection('', []));
		$subscription = $subscriptions->current()->print(new Output\FakeFormat(''))->serialization();
		Assert::contains('|id|1|', $subscription);
		$subscriptions->next();
		$subscription = $subscriptions->current()->print(new Output\FakeFormat(''))->serialization();
		Assert::contains('|id|4|', $subscription);
		$subscriptions->next();
		$subscription = $subscriptions->current()->print(new Output\FakeFormat(''))->serialization();
		Assert::contains('|id|3|', $subscription);
		$subscriptions->next();
		Assert::null($subscriptions->current());
	}

	public function testIteratingWithoutVisits() {
		(new Misc\SamplePart($this->database))->try();
		(new Misc\SampleSubscription($this->database, ['user' => 1, 'part' => 1]))->try();
		$this->truncate(['part_visits']);
		$subscriptions = (new Subscribing\OwnedSubscriptions(
			new Access\FakeUser('1'),
			$this->database
		))->all(new Dataset\FakeSelection('', []));
		$subscription = $subscriptions->current();
		Assert::notSame(null, $subscription);
	}

	public function testEmptyIterating() {
		$subscriptions = (new Subscribing\OwnedSubscriptions(
			new Access\FakeUser('1'),
			$this->database
		))->all(new Dataset\FakeSelection('', []));
		Assert::null($subscriptions->current());
	}
}

(new OwnedSubscriptions)->run();