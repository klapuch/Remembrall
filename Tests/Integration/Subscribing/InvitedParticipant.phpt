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

final class InvitedParticipant extends \Tester\TestCase {
	use TestCase\Database;

	public function testKickingWithRemovingAllProofs() {
		$participant = 'me@participant.cz';
		$participants = new Subscribing\InvitedParticipant($this->database, 2, $participant);
		(new Misc\SampleParticipant($this->database, ['subscription' => 2, 'email' => $participant]))->try();
		$participants->kick();
		(new Misc\SampleParticipant($this->database, ['subscription' => 3, 'email' => $participant]))->try();
		(new Misc\TableCount($this->database, 'participants', 1))->assert();
		(new Misc\TableCount($this->database, 'invitation_attempts', 2))->assert();
		Assert::same(3, $this->database->query('SELECT subscription_id FROM participants')->fetchColumn());
	}

	public function testKickingByCaseInsensitiveEmail() {
		$participant = 'me@participant.cz';
		(new Misc\SampleParticipant($this->database, ['subscription' => 2, 'email' => $participant]))->try();
		(new Subscribing\InvitedParticipant($this->database, 2, strtoupper($participant)))->kick();
		(new Misc\TableCount($this->database, 'participants', 0))->assert();
		(new Misc\SampleParticipant($this->database, ['subscription' => 2, 'email' => strtoupper($participant)]))->try();
		(new Subscribing\InvitedParticipant($this->database, 2, $participant))->kick();
		(new Misc\TableCount($this->database, 'participants', 0))->assert();
	}

	/**
	 * @throws \UnexpectedValueException Email "me@participant.cz" is not your participant
	 */
	public function testThrowingOnKickingUnknownParticipant() {
		$participant = 'me@participant.cz';
		(new Subscribing\InvitedParticipant(
			$this->database,
			2,
			$participant
		))->kick();
	}

	public function testKickingWithCaseInsensitiveEmail() {
		[$participant, $subscription] = ['me@participant.cz', 2];
		(new Misc\SampleParticipant($this->database, ['email' => $participant, 'subscription' => $subscription]))->try();
		Assert::noError(function() use ($participant, $subscription) {
			(new Subscribing\InvitedParticipant(
				$this->database,
				$subscription,
				strtoupper($participant)
			))->kick();
		});
		$this->truncate(['participants']);
		(new Misc\SampleParticipant($this->database, ['email' => strtoupper($participant), 'subscription' => $subscription]))->try();
		Assert::noError(function() use ($participant, $subscription) {
			(new Subscribing\InvitedParticipant(
				$this->database,
				$subscription,
				$participant
			))->kick();
		});
	}

	public function testPrinting() {
		[$participant, $subscription] = ['me@participant.cz', 2];
		(new Misc\SampleParticipant(
			$this->database,
			[
				'email' => $participant,
				'subscription' => $subscription,
				'invited_at' => '2017-09-02 11:16:06',
				'accepted' => false,
				'decided_at' => 'NULL',
			]
		))->try();
		Assert::same(
			'|id|1||email|me@participant.cz||subscription_id|2||harassed|||invited_at|2017-09-02 11:16:06+00||accepted|||decided_at||',
			(new Subscribing\InvitedParticipant(
				$this->database,
				$subscription,
				$participant
			))->print(new Output\FakeFormat())->serialization()
		);
	}
}

(new InvitedParticipant)->run();