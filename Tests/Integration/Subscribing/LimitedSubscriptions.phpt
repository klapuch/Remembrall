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
use Remembrall\Misc;
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class LimitedSubscriptions extends \Tester\TestCase {
	use TestCase\Database;

	public function testPassingOnSubscribingInLimit() {
		Assert::noError(
			function() {
				(new Subscribing\LimitedSubscriptions(
					new Subscribing\FakeSubscriptions(),
					new Access\FakeUser('666'),
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

	public function testThrowingOnSubscribingOverLimit() {
		$this->database->exec(
			"INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES
			(1, 'www.google.com', ROW('//a', 'xpath'), 'a', ''),
			(2, 'www.facedown.cz', ROW('//b', 'xpath'), 'b', ''),
			(3, 'www.facedown.cz', ROW('//c', 'xpath'), 'c', ''),
			(4, 'www.google.com', ROW('//d', 'xpath'), 'd', ''),
			(5, 'www.facedown.cz', ROW('//d', 'xpath'), 'd', '')"
		);
		(new Misc\SampleSubscription($this->database, ['user' => 666, 'part' => 1]))->try();
		(new Misc\SampleSubscription($this->database, ['user' => 666, 'part' => 2]))->try();
		(new Misc\SampleSubscription($this->database, ['user' => 666, 'part' => 3]))->try();
		(new Misc\SampleSubscription($this->database, ['user' => 666, 'part' => 4]))->try();
		(new Misc\SampleSubscription($this->database, ['user' => 666, 'part' => 5]))->try();
		$ex = Assert::exception(function() {
			(new Subscribing\LimitedSubscriptions(
				new Subscribing\FakeSubscriptions(),
				new Access\FakeUser('666'),
				$this->database
			))->subscribe(
				new Uri\FakeUri('url'),
				'//p',
				'xpath',
				new Time\FakeInterval()
			);
		}, \UnexpectedValueException::class, 'You have reached the limit of 5 subscribed parts');
		Assert::type(\Throwable::class, $ex->getPrevious());
	}
}

(new LimitedSubscriptions)->run();