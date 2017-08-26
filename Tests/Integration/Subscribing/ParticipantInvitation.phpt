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

final class ParticipantInvitation extends \Tester\TestCase {
	use TestCase\Database;

	public function testAcceptingInvitationWithKnownCode() {
		$code = 'abc';
		(new Misc\SampleParticipant($this->database, ['code' => $code]))->try();
		(new Subscribing\ParticipantInvitation(
			$code,
			$this->database
		))->accept();
		$participant = $this->database->query('SELECT * FROM participants')->fetch();
		Assert::true($participant['accepted']);
		Assert::same(
			(new \DateTime($this->database->query('SELECT NOW()')->fetchColumn()))->format('Y-m-d'),
			(new \DateTime($participant['decided_at']))->format('Y-m-d')
		);
	}

	public function testDecliningWithCapturedDecision() {
		$code = 'abc';
		(new Misc\SampleParticipant($this->database, ['code' => $code, 'accepted' => false]))->try();
		(new Subscribing\ParticipantInvitation(
			$code,
			$this->database
		))->decline();
		$participant = $this->database->query('SELECT * FROM participants')->fetch();
		Assert::false($participant['accepted']);
		Assert::same(
			(new \DateTime($this->database->query('SELECT NOW()')->fetchColumn()))->format('Y-m-d'),
			(new \DateTime($participant['decided_at']))->format('Y-m-d')
		);
	}

	public function testPrintingEmailInformation() {
		$code = 'abc';
		$this->database->prepare(
			"INSERT INTO participants (id, email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			(1, 'me@participant.cz', 2, ?, NOW(), FALSE, NULL)"
		)->execute([$code]);
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
		$participant = (new Subscribing\ParticipantInvitation(
			$code,
			$this->database
		))->print(new Output\FakeFormat())->serialization();
		Assert::same(
			'|email|me@participant.cz||code|abc||author|author@participant.cz||expression|//p||url|www.me.cz|',
			$participant
		);
	}
}

(new ParticipantInvitation)->run();