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

final class InvitedParticipants extends \Tester\TestCase {
	use TestCase\Database;

	public function testThrowingOnKickingUnknownParticipant() {
		[$participant, $subscription] = ['me@participant.cz', 2];
		(new Misc\SampleParticipant($this->database, ['email' => $participant]))->try();
		Assert::exception(function() use ($participant, $subscription) {
			(new Subscribing\InvitedParticipants(
				new Subscribing\FakeParticipants(),
				$this->database
			))->kick(2, $participant);
		}, \UnexpectedValueException::class, 'Email "me@participant.cz" is not your participant');
	}

	public function testThrowingOnKickingUnknownCaseInsensitiveParticipant() {
		[$participant, $subscription] = ['me@participant.cz', 2];
		(new Misc\SampleParticipant($this->database, ['email' => $participant, 'subscription' => $subscription]))->try();
		$participants = new Subscribing\InvitedParticipants(
			new Subscribing\FakeParticipants(new Subscribing\FakeInvitation()),
			$this->database
		);
		Assert::noError(function() use ($participant, $participants, $subscription) {
			$participants->kick($subscription, strtoupper($participant));
		});
		$this->truncate(['participants']);
		(new Misc\SampleParticipant($this->database, ['email' => $participant, 'subscription' => $subscription]))->try();
		Assert::noError(function() use ($participant, $participants, $subscription) {
			$participants->kick($subscription, $participant);
		});
	}

	public function testThrowingOnInvitingCaseInsensitiveAcceptedParticipant() {
		[$participant, $subscription] = ['me@participant.cz', 1];
		(new Misc\SampleParticipant(
			$this->database,
			['email' => $participant, 'subscription' => $subscription, 'accepted' => true]
		))->try();
		Assert::exception(function() use ($subscription, $participant) {
			(new Subscribing\InvitedParticipants(
				new Subscribing\FakeParticipants(),
				$this->database
			))->invite($subscription, strtoupper($participant));
		}, \UnexpectedValueException::class, 'Email "ME@PARTICIPANT.CZ" is already your participant');
		$this->truncate(['participants']);
		(new Misc\SampleParticipant(
			$this->database,
			['email' => strtoupper($participant), 'subscription' => $subscription, 'accepted' => true]
		))->try();
		Assert::exception(function() use ($subscription, $participant) {
			(new Subscribing\InvitedParticipants(
				new Subscribing\FakeParticipants(),
				$this->database
			))->invite($subscription, strtolower($participant));
		}, \UnexpectedValueException::class, 'Email "me@participant.cz" is already your participant');
	}

	public function testThrowingOnInvitingAcceptedParticipant() {
		[$participant, $subscription] = ['me@participant.cz', 1];
		(new Misc\SampleParticipant(
			$this->database,
			['email' => $participant, 'subscription' => $subscription, 'accepted' => true]
		))->try();
		Assert::exception(function() use ($subscription, $participant) {
			(new Subscribing\InvitedParticipants(
				new Subscribing\FakeParticipants(new Subscribing\FakeInvitation()),
				$this->database
			))->invite($subscription, $participant);
		}, \UnexpectedValueException::class, 'Email "me@participant.cz" is already your participant');
		$this->truncate(['participants']);
		(new Misc\SampleParticipant(
			$this->database,
			['email' => $participant, 'accepted' => true]
		))->try();
		Assert::noError(function() use ($subscription, $participant) {
			(new Subscribing\InvitedParticipants(
				new Subscribing\FakeParticipants(new Subscribing\FakeInvitation()),
				$this->database
			))->invite($subscription, $participant);
		});
	}
}

(new InvitedParticipants)->run();