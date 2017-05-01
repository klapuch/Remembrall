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

final class GuestParticipants extends TestCase\Database {
	/**
	 * @throws \UnexpectedValueException Email "me@participant.cz" is registered and can not be participant
	 */
	public function testThrowingOnInvitingRegisteredEmail() {
		$this->database->exec(
			"INSERT INTO users (email, password, role) VALUES ('me@participant.cz', 'heslo', 'member')"
		);
		(new Subscribing\GuestParticipants(
			new Subscribing\FakeParticipants(),
			$this->database
		))->invite(1, 'me@participant.cz');
	}

	public function testThrowingOnInvitingRegisteredCaseInsensitiveEmail() {
		$this->database->exec(
			"INSERT INTO users (email, password, role) VALUES ('me@participant.cz', 'heslo', 'member')"
		);
		Assert::exception(function() {
			(new Subscribing\GuestParticipants(
				new Subscribing\FakeParticipants(),
				$this->database
			))->invite(1, 'ME@participant.cz');
		}, \UnexpectedValueException::class);
		$this->database->exec(
			"INSERT INTO users (email, password, role) VALUES ('YOU@participant.cz', 'heslo', 'member')"
		);
		Assert::exception(function() {
			(new Subscribing\GuestParticipants(
				new Subscribing\FakeParticipants(),
				$this->database
			))->invite(1, 'you@participant.cz');
		}, \UnexpectedValueException::class);
	}

	public function testPassingWithNotRegisteredEmail() {
		Assert::equal(
			new Subscribing\FakeInvitation(),
			(new Subscribing\GuestParticipants(
				new Subscribing\FakeParticipants(new Subscribing\FakeInvitation()),
				$this->database
			))->invite(1, 'me@participant.cz')
		);
	}

	protected function prepareDatabase(): void {
		$this->purge(['users']);
	}
}

(new GuestParticipants)->run();