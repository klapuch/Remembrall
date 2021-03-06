<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Subscribing;

use Klapuch\Access;
use Klapuch\Output;
use Remembrall\Misc;
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class NonViolentParticipants extends \Tester\TestCase {
	use TestCase\Database;

	public function testInvitingBrandNewParticipant() {
		(new Subscribing\NonViolentParticipants(
			new Access\FakeUser(),
			$this->database
		))->invite(2, 'me@participant.cz');
		(new Misc\TableCount($this->database, 'participants', 1))->assert();
		$participants = $this->database->query('SELECT * FROM participants')->fetchAll();
		Assert::same('me@participant.cz', $participants[0]['email']);
		Assert::same(2, $participants[0]['subscription_id']);
		Assert::false($participants[0]['accepted']);
		Assert::null($participants[0]['decided_at']);
		Assert::match('~[0-9a-fA-F]{64}~', $participants[0]['code']);
		Assert::same(
			(new \DateTime($this->database->query('SELECT NOW()')->fetchColumn()))->format('Y-m-d'),
			(new \DateTime($participants[0]['invited_at']))->format('Y-m-d')
		);
	}

	public function testInvitingSameParticipantLeadingToUpdate() {
		$participants = new Subscribing\NonViolentParticipants(new Access\FakeUser(), $this->database);
		$participants->invite(2, 'me@participant.cz');
		$statement = $this->database->prepare('SELECT * FROM participants');
		$statement->execute();
		$rows = $statement->fetchAll();
		$participants->invite(2, 'me@participant.cz');
		$statement->execute();
		(new Misc\TableCount($this->database, 'participants', 1))->assert();
		Assert::same(['invited_at'], array_keys(array_diff($rows[0], $statement->fetch())));
	}

	public function testInvitingWithCaseSensitiveEmailWithoutAdding() {
		$participants = new Subscribing\NonViolentParticipants(new Access\FakeUser(), $this->database);
		$participants->invite(2, 'me@participant.cz');
		$participants->invite(2, 'ME@participant.cz');
		(new Misc\TableCount($this->database, 'participants', 1))->assert();
	}

	public function testInvitingAgainAfterDeniedDecision() {
		[$participant, $subscription] = ['me@participant.cz', 1];
		(new Misc\SampleParticipant(
			$this->database,
			['email' => $participant, 'accepted' => false]
		))->try();
		$participants = new Subscribing\NonViolentParticipants(new Access\FakeUser(), $this->database);
		Assert::noError(function() use ($participant, $participants, $subscription) {
			$participants->invite($subscription, $participant);
		});
	}

	public function testInvitationWithCreatedCode() {
		$participant = 'me@participant.cz';
		$participants = new Subscribing\NonViolentParticipants(new Access\FakeUser(), $this->database);
		$invitation = $participants->invite(2, $participant);
		Assert::equal(
			new Subscribing\ParticipantInvitation(
				$this->database->query('SELECT code FROM participants')->fetchColumn(),
				$this->database
			),
			$invitation
		);
	}

	public function testPrintingAuthorsParticipants() {
		$this->database->exec(
			"INSERT INTO participants (id, email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			(1, 'owned@participant.cz', 2, 'a', NOW(), FALSE, '2000-01-01'),
			(2, 'foo@participant.cz', 3, 'c', NOW(), FALSE, NULL),
			(3, 'owned2@participant.cz', 2, 'b', NOW(), FALSE, '1999-01-01'),
			(4, 'bar@participant.cz', 3, 'd', NOW(), FALSE, NULL)"
		);
		$this->database->exec(
			"INSERT INTO subscriptions (id, user_id, part_id, interval, last_update, snapshot) VALUES 
			(2, 3, 4, 'PT10S', '2000-01-01', 'aa'),
			(3, 2, 4, 'PT5S', '2001-01-01', 'ab')"
		);
		(new Misc\SampleUser($this->database))->try();
		(new Misc\SampleUser($this->database))->try();
		(new Misc\SampleUser($this->database))->try();
		$this->truncate(['invitation_attempts']);
		$this->database->exec(
			'INSERT INTO invitation_attempts (id, participant_id, attempt_at) VALUES
			(1, 1, NOW()), (2, 1, NOW()), (3, 1, NOW()), (4, 1, NOW()), (5, 1, NOW())'
		);
		$participants = (new Subscribing\NonViolentParticipants(
			new Access\FakeUser('3'),
			$this->database
		))->all();
		$print = $participants->current()->print(new Output\FakeFormat())->serialization();
		Assert::contains('|id|1||email|owned@participant.cz||subscription_id|2||harassed||', $print);
		Assert::contains('|invited_at|', $print);
		Assert::contains('||accepted|||decided_at|', $print);
		$participants->next();
		$print = $participants->current()->print(new Output\FakeFormat())->serialization();
		Assert::contains('|id|3||email|owned2@participant.cz||subscription_id|2||harassed||', $print);
		$participants->next();
		Assert::null($participants->current());
	}

	public function testInvitingWithoutSpamming() {
		[$subscription, $participant] = [2, 'me@participant.cz'];
		$participants = new Subscribing\NonViolentParticipants(
			new Access\FakeUser(),
			$this->database
		);
		$participants->invite($subscription, $participant);
		$participants->invite($subscription, $participant);
		$participants->invite($subscription, $participant);
		$participants->invite($subscription, $participant);
		$participants->invite($subscription, $participant);
		Assert::exception(function() use ($participant, $participants, $subscription) {
			$participants->invite($subscription, $participant);
		}, \UnexpectedValueException::class, '"me@participant.cz" declined your invitation too many times');
		Assert::same(5, $this->database->query('SELECT COUNT(*) FROM invitation_attempts')->fetchColumn());
		$this->database->exec("UPDATE invitation_attempts SET attempt_at = NOW() - INTERVAL '12 HOUR'");
		$participants->invite($subscription, $participant);
		$participants->invite($subscription, $participant);
		$participants->invite($subscription, $participant);
		$participants->invite($subscription, $participant);
		$participants->invite($subscription, $participant);
		Assert::exception(function() use ($participant, $participants, $subscription) {
			$participants->invite($subscription, $participant);
		}, \UnexpectedValueException::class, '"me@participant.cz" declined your invitation too many times');
		Assert::noError(function() use ($subscription, $participant, $participants) {
			$participants->invite($subscription, 'you@participant.cz');
			$participants->invite($subscription + 1, $participant);
		});
	}
}

(new NonViolentParticipants)->run();