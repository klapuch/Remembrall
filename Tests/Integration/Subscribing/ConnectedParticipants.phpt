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

final class ConnectedParticipants extends TestCase\Database {
	public function testInvitingBrandNewParticipant() {
		(new Subscribing\ConnectedParticipants(
			2,
			$this->database
		))->invite('me@participant.cz');
		$participants = $this->database->query('SELECT * FROM participants')->fetchAll();
		Assert::count(1, $participants);
		Assert::same('me@participant.cz', $participants[0]['email']);
		Assert::same(2, $participants[0]['subscription_id']);
		Assert::false($participants[0]['accepted']);
		Assert::null($participants[0]['decided_at']);
		Assert::match('~[0-9a-fA-F]{64}~', $participants[0]['code']);
		Assert::same(
			(new \DateTime())->format('Y-m-d'),
			(new \DateTime($participants[0]['invited_at']))->format('Y-m-d')
		);
	}

	public function testInvitingSameParticipantLeadingToUpdate() {
		$participants = new Subscribing\ConnectedParticipants(2, $this->database);
		$participants->invite('me@participant.cz');
		$statement = $this->database->prepare('SELECT * FROM participants');
		$statement->execute();
		$rows = $statement->fetchAll();
		$participants->invite('me@participant.cz');
		$statement->execute();
		$updatedRows = $statement->fetchAll();
		Assert::count(1, $updatedRows);
		Assert::same(['invited_at'], array_keys(array_diff($rows[0], $updatedRows[0])));
	}

	public function testInvitingWithCaseSensitiveEmail() {
		$participants = new Subscribing\ConnectedParticipants(2, $this->database);
		$participants->invite('me@participant.cz');
		$participants->invite('ME@participant.cz');
		$statement = $this->database->prepare('SELECT * FROM participants');
		$statement->execute();
		$rows = $statement->fetchAll();
		Assert::count(1, $rows);
	}

	public function testInvitingAgainWithDeniedDecision() {
		[$participant, $subscription] = ['me@participant.cz', 1];
		$statement = $this->database->prepare(
			"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			(?, ?, '123', NOW(), FALSE, NOW())"
		);
		$statement->execute([$participant, $subscription]);
		$participants = new Subscribing\ConnectedParticipants($subscription, $this->database);
		Assert::noError(function() use ($participant, $participants) {
			$participants->invite($participant);
		});
	}

	public function testKickingWithRemovingAllProofs() {
		$participant = 'me@participant.cz';
		$participants = new Subscribing\ConnectedParticipants(2, $this->database);
		$participants->invite($participant);
		$participants->kick($participant);
		(new Subscribing\ConnectedParticipants(
			3,
			$this->database
		))->invite($participant);
		Assert::count(1, $this->database->query('SELECT * FROM participants')->fetchAll());
		Assert::same(3, $this->database->query('SELECT subscription_id FROM participants')->fetchColumn());
	}

	public function testKickingByCaseInsensitiveEmail() {
		$participant = 'me@participant.cz';
		$participants = new Subscribing\ConnectedParticipants(2, $this->database);
		$participants->invite($participant);
		$participants->kick(strtoupper($participant));
		Assert::count(0, $this->database->query('SELECT * FROM participants')->fetchAll());
		$participants->invite(strtoupper($participant));
		$participants->kick($participant);
		Assert::count(0, $this->database->query('SELECT * FROM participants')->fetchAll());
	}

	public function testPrintingEverythingFromPassedSubscription() {
		$participants = new Subscribing\ConnectedParticipants(2, $this->database);
		$participants->invite('me@participant.cz');
		$participants->invite('you@participant.cz');
		(new Subscribing\ConnectedParticipants(
			3,
			$this->database
		))->invite('me@participant.cz');
		$prints = $participants->print(new Output\FakeFormat());
		$print = $prints->current()->serialization();
		Assert::contains('|email|me@participant.cz||subscription_id|2|', $print);
		Assert::contains('|invited_at|', $print);
		Assert::contains('|accepted|||decided_at||', $print);
		$prints->next();
		$print = $prints->current()->serialization();
		Assert::contains('|email|you@participant.cz||subscription_id|2|', $print);
		$prints->next();
		Assert::null($prints->current());
	}

	protected function prepareDatabase(): void {
		$this->purge(['participants']);
	}
}

(new ConnectedParticipants)->run();