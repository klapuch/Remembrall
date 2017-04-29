<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Subscribing;

use Klapuch\Output;
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class MemorialInvitation extends TestCase\Database {
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
			(4, 'www.me.cz', '//p', 'foo', 'as')"
		);
		$this->database->exec(
			"INSERT INTO subscriptions (id, user_id, part_id, interval, last_update, snapshot) VALUES 
			(2, 3, 4, 'PT10S', '2000-01-01', 'as')"
		);
		$this->database->exec(
			"INSERT INTO users (id, email, password) VALUES 
			(3, 'author@participant.cz', 'heslo')"
		);
		$print = (new Subscribing\MemorialInvitation(
			2,
			'me@participant.cz',
			$this->database
		))->print(new Output\FakeFormat());
		Assert::same('|author|author@participant.cz||expression|//p||url|www.me.cz|', $print->serialization());
	}

	protected function prepareDatabase(): void {
		$this->purge(['participants', 'subscriptions', 'users', 'parts', 'invitation_attempts']);
	}
}

(new MemorialInvitation())->run();