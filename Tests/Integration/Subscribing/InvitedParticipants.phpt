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

final class InvitedParticipants extends TestCase\Database {
	public function testThrowingOnKickingUnknownParticipant() {
		[$participant, $subscription] = ['me@participant.cz', 2];
		$statement = $this->database->prepare(
			"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			(?, ?, '123', NOW(), FALSE, NULL)"
		);
		$statement->execute([$participant, $subscription + 1]);
		Assert::exception(function() use ($participant, $subscription) {
			(new Subscribing\InvitedParticipants(
				new Subscribing\FakeParticipants(),
				$subscription,
				$this->database
			))->kick($participant);
		}, \Remembrall\Exception\NotFoundException::class, 'Email "me@participant.cz" is not your participant');
	}

	public function testThrowingOnKickingUnknownCaseInsensitiveParticipant() {
		[$participant, $subscription] = ['me@participant.cz', 2];
		$statement = $this->database->prepare(
			"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			(?, ?, '123', NOW(), FALSE, NULL)"
		);
		$statement->execute([$participant, $subscription]);
		$participants = new Subscribing\InvitedParticipants(
			new Subscribing\FakeParticipants(),
			$subscription,
			$this->database
		);
		Assert::noError(function() use ($participant, $participants) {
			$participants->kick(strtoupper($participant));
		});
		$this->prepareDatabase();
		$statement->execute([strtoupper($participant), $subscription]);
		Assert::noError(function() use ($participant, $participants) {
			$participants->kick($participant);
		});
	}

	public function testThrowingOnInvitingCaseInsensitiveAcceptedParticipant() {
		[$participant, $subscription] = ['me@participant.cz', 1];
		$statement = $this->database->prepare(
			"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			(?, ?, '123', NOW(), TRUE, NOW())"
		);
		$statement->execute([$participant, $subscription]);
		Assert::exception(function() use ($subscription, $participant) {
			(new Subscribing\InvitedParticipants(
				new Subscribing\FakeParticipants(),
				$subscription,
				$this->database
			))->invite(strtoupper($participant));
		}, \Remembrall\Exception\DuplicateException::class, 'Email "ME@PARTICIPANT.CZ" is already your participant');
		$this->prepareDatabase();
		$statement->execute([strtoupper($participant), $subscription]);
		Assert::exception(function() use ($subscription, $participant) {
			(new Subscribing\InvitedParticipants(
				new Subscribing\FakeParticipants(),
				$subscription,
				$this->database
			))->invite(strtolower($participant));
		}, \Remembrall\Exception\DuplicateException::class, 'Email "me@participant.cz" is already your participant');
	}

	public function testThrowingOnInvitingAcceptedParticipant() {
		[$participant, $subscription] = ['me@participant.cz', 1];
		$statement = $this->database->prepare(
			"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			(?, ?, '123', NOW(), TRUE, NOW())"
		);
		$statement->execute([$participant, $subscription]);
		Assert::exception(function() use ($subscription, $participant) {
			(new Subscribing\InvitedParticipants(
				new Subscribing\FakeParticipants(),
				$subscription,
				$this->database
			))->invite($participant);
		}, \Remembrall\Exception\DuplicateException::class, 'Email "me@participant.cz" is already your participant');
		$this->prepareDatabase();
		$statement->execute([$participant, $subscription + 1]);
		Assert::noError(function() use ($subscription, $participant) {
			(new Subscribing\InvitedParticipants(
				new Subscribing\FakeParticipants(),
				$subscription,
				$this->database
			))->invite($participant);
		});
	}

	protected function prepareDatabase(): void {
		$this->purge(['participants']);
	}
}

(new InvitedParticipants)->run();