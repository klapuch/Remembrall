<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Subscribing;

use Klapuch\Output;
use Remembrall\Misc;
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class MemorialInvitation extends \Tester\TestCase {
	use TestCase\Database;

	public function testThrowingOnManipulation() {
		Assert::exception(function() {
			(new Subscribing\MemorialInvitation(
				1,
				'foo@bar.cz',
				$this->database
			))->accept();
		}, \LogicException::class, 'Memorial invitation can not be accepted');
		Assert::exception(function() {
			(new Subscribing\MemorialInvitation(
				1,
				'foo@bar.cz',
				$this->database
			))->decline();
		}, \LogicException::class, 'Memorial invitation can not be declined');
	}

	public function testPrintingMemorialInfo() {
		$this->database->exec(
			"INSERT INTO participants (id, email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			(1, 'me@participant.cz', 2, 'abc', NOW(), FALSE, NULL)"
		);
		$this->database->exec(
			"INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES 
			(4, 'www.me.cz', ROW('//p', 'xpath'), 'foo', 'as')"
		);
		$this->database->exec(
			"INSERT INTO subscriptions (id, user_id, part_id, interval, last_update, snapshot) VALUES 
			(2, 3, 4, 'PT10S', '2000-01-01', 'as')"
		);
		(new Misc\SampleUser($this->database))->try();
		(new Misc\SampleUser($this->database))->try();
		(new Misc\SampleUser($this->database, ['email' => 'author@participant.cz']))->try();
		$print = (new Subscribing\MemorialInvitation(
			2,
			'me@participant.cz',
			$this->database
		))->print(new Output\FakeFormat());
		Assert::same('|author|author@participant.cz||expression|//p||email|me@participant.cz||code|abc||url|www.me.cz|', $print->serialization());
	}
}

(new MemorialInvitation())->run();