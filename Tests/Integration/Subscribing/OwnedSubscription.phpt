<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use Remembrall\Model\{
	Subscribing, Access
};
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class OwnedSubscription extends TestCase\Database {
	/**
	 * @throws \Remembrall\Exception\NotFoundException You do not own this part
	 */
	public function testCancelingForeign() {
		(new Subscribing\OwnedSubscription(
			'www.google.com',
			'//b',
			new Access\FakeSubscriber(666),
			$this->database
		))->cancel();
	}

	public function testCancelingOwned() {
		(new Subscribing\OwnedSubscription(
			'www.facedown.cz',
			'//b',
			new Access\FakeSubscriber(666),
			$this->database
		))->cancel();
		$parts = $this->database->fetchAll('SELECT id FROM subscriptions');
		Assert::count(1, $parts);
		Assert::same(1, $parts[0]['id']);
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException You do not own this part
	 */
	public function testEditingForeign() {
		(new Subscribing\OwnedSubscription(
			'www.google.com',
			'//b',
			new Access\FakeSubscriber(666),
			$this->database
		))->edit(new Subscribing\FakeInterval());
	}

	public function testEditingOwned() {
		(new Subscribing\OwnedSubscription(
			'www.facedown.cz',
			'//b',
			new Access\FakeSubscriber(666),
			$this->database
		))->edit(
			new Subscribing\FakeInterval(
				new \DateTimeImmutable('15:00'),
				null,
				new \DateInterval('PT44M')
			)
		);
		$parts = $this->database->fetchAll('SELECT id, interval FROM subscriptions');
		Assert::count(2, $parts);
		Assert::same(1, $parts[0]['id']);
		Assert::same('PT2M', $parts[0]['interval']);
		Assert::same(2, $parts[1]['id']);
		Assert::same('PT44M', $parts[1]['interval']);
	}

	protected function prepareDatabase() {
		$this->purge(['parts', 'subscriptions']);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.google.com", "//b", "b"),
			("www.facedown.cz", "//b", "c")'
		);
		$this->database->query(
			'INSERT INTO subscriptions (part_id, subscriber_id, interval) VALUES
			(1, 2, "PT2M"),
			(2, 666, "PT3M")'
		);
	}
}

(new OwnedSubscription)->run();
