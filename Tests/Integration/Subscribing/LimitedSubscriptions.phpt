<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Subscribing;

use Klapuch\Access;
use Klapuch\Time;
use Klapuch\Uri;
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class LimitedSubscriptions extends \Tester\TestCase {
	use TestCase\Database;

	public function testSubscribingInLimit() {
		Assert::noError(
			function() {
				(new Subscribing\LimitedSubscriptions(
					new Subscribing\FakeSubscriptions(),
					new Access\FakeUser(666),
					$this->database
				))->subscribe(
					new Uri\FakeUri('url'),
					'//p',
					'xpath',
					new Time\FakeInterval()
				);
			}
		);
	}

	public function testSubscribingOverLimit() {
		$this->database->exec(
			"INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES
			(1, 'www.google.com', '//a', 'a', ''),
			(2, 'www.facedown.cz', '//b', 'b', ''),
			(3, 'www.facedown.cz', '//c', 'c', ''),
			(4, 'www.google.com', '//d', 'd', ''),
			(5, 'www.facedown.cz', '//d', 'd', '')"
		);
		$this->database->exec(
			"INSERT INTO subscriptions (part_id, user_id, interval, last_update, snapshot) VALUES
			(1, 666, 'PT1M', NOW(), ''),
			(2, 666, 'PT2M', NOW(), ''),
			(3, 666, 'PT3M', NOW(), ''),
			(4, 666, 'PT4M', NOW(), ''),
			(5, 666, 'PT5M', NOW(), '')"
		);
		$ex = Assert::exception(function() {
			(new Subscribing\LimitedSubscriptions(
				new Subscribing\FakeSubscriptions(),
				new Access\FakeUser(666),
				$this->database
			))->subscribe(
				new Uri\FakeUri('url'),
				'//p',
				'xpath',
				new Time\FakeInterval()
			);
		}, \OverflowException::class, 'You have reached the limit of 5 subscribed parts');
		Assert::type(\Throwable::class, $ex->getPrevious());
	}
}

(new LimitedSubscriptions)->run();