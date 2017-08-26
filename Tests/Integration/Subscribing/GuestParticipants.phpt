<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Subscribing;

use Remembrall\Misc;
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class GuestParticipants extends \Tester\TestCase {
	use TestCase\Database;

	/**
	 * @throws \UnexpectedValueException Email "me@participant.cz" is registered and can not be participant
	 */
	public function testThrowingOnInvitingRegisteredEmail() {
		(new Misc\SampleUser($this->database, ['email' => 'me@participant.cz']))->try();
		(new Subscribing\GuestParticipants(
			new Subscribing\FakeParticipants(),
			$this->database
		))->invite(1, 'me@participant.cz');
	}

	public function testThrowingOnInvitingRegisteredCaseInsensitiveEmail() {
		(new Misc\SampleUser($this->database, ['email' => 'me@participant.cz']))->try();
		Assert::exception(function() {
			(new Subscribing\GuestParticipants(
				new Subscribing\FakeParticipants(),
				$this->database
			))->invite(1, 'ME@participant.cz');
		}, \UnexpectedValueException::class);
		(new Misc\SampleUser($this->database, ['email' => 'YOU@participant.cz']))->try();
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
}

(new GuestParticipants)->run();