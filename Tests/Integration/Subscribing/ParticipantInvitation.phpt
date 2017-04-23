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

final class ParticipantInvitation extends TestCase\Database {
	public function testAcceptingInvitationWithKnownCode() {
		$code = 'abc';
		$this->database->prepare(
			"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			('me@participant.cz', 1, ?, NOW(), FALSE, NULL)"
		)->execute([$code]);
		(new Subscribing\ParticipantInvitation(
			$code,
			$this->database
		))->accept();
		$participant = $this->database->query('SELECT * FROM participants')->fetch();
		Assert::true($participant['accepted']);
		Assert::same(
			(new \DateTime())->format('Y-m-d'),
			(new \DateTime($participant['decided_at']))->format('Y-m-d')
		);
	}

	public function testThrowingOnUnknownCode() {
		Assert::exception(function() {
			(new Subscribing\ParticipantInvitation(
				'abcd',
				$this->database
			))->accept();
		}, \Remembrall\Exception\NotFoundException::class, 'The invitation is accepted or does not exist');
		Assert::exception(function() {
			(new Subscribing\ParticipantInvitation(
				'abcd',
				$this->database
			))->deny();
		}, \Remembrall\Exception\NotFoundException::class, 'The invitation is denied or does not exist');
		$this->database->prepare(
			"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			('me@participant.cz', 1, 'abc', NOW(), FALSE, NULL)"
		)->execute();
		Assert::exception(function() {
			(new Subscribing\ParticipantInvitation(
				'abcd',
				$this->database
			))->accept();
		}, \Remembrall\Exception\NotFoundException::class, 'The invitation is accepted or does not exist');
		Assert::exception(function() {
			(new Subscribing\ParticipantInvitation(
				'abcd',
				$this->database
			))->deny();
		}, \Remembrall\Exception\NotFoundException::class, 'The invitation is denied or does not exist');
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException The invitation is accepted or does not exist
	 */
	public function testThrowingOnAcceptingAlreadyAcceptedCode() {
		$code = 'abc';
		$this->database->prepare(
			"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			('me@participant.cz', 1, ?, NOW(), TRUE, NULL)"
		)->execute([$code]);
		(new Subscribing\ParticipantInvitation(
			$code,
			$this->database
		))->accept();
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException The invitation is accepted or does not exist
	 */
	public function testThrowingOnAcceptingCaseInsensitiveCode() {
		$code = 'abc';
		$this->database->prepare(
			"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			('me@participant.cz', 1, ?, NOW(), FALSE, NULL)"
		)->execute([$code]);
		(new Subscribing\ParticipantInvitation(
			strtoupper($code),
			$this->database
		))->accept();
	}

	public function testDenyingWithCapturedDecision() {
		$code = 'abc';
		$this->database->prepare(
			"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			('me@participant.cz', 1, ?, NOW(), FALSE, NULL)"
		)->execute([$code]);
		(new Subscribing\ParticipantInvitation(
			$code,
			$this->database
		))->deny();
		$participant = $this->database->query('SELECT * FROM participants')->fetch();
		Assert::false($participant['accepted']);
		Assert::same(
			(new \DateTime())->format('Y-m-d'),
			(new \DateTime($participant['decided_at']))->format('Y-m-d')
		);
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException The invitation is denied or does not exist
	 */
	public function testThrowingOnDenyingCaseInsensitiveCode() {
		$code = 'abc';
		$this->database->prepare(
			"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			('me@participant.cz', 1, ?, NOW(), FALSE, NULL)"
		)->execute([$code]);
		(new Subscribing\ParticipantInvitation(
			strtoupper($code),
			$this->database
		))->deny();
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException The invitation is denied or does not exist
	 */
	public function testThrowingOnDenyAlreadyDeniedCode() {
		$code = 'abc';
		$this->database->prepare(
			"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			('me@participant.cz', 1, ?, NOW(), FALSE, NOW())"
		)->execute([$code]);
		(new Subscribing\ParticipantInvitation(
			$code,
			$this->database
		))->deny();
	}

	public function testPrintingEmailableInformation() {
		$code = 'abc';
		$this->database->prepare(
			"INSERT INTO participants (id, email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			(1, 'me@participant.cz', 2, ?, NOW(), FALSE, NULL)"
		)->execute([$code]);
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
		$participant = (new Subscribing\ParticipantInvitation(
			$code,
			$this->database
		))->print(new Output\FakeFormat())->serialization();
		Assert::same(
			'|email|me@participant.cz||code|abc||author|author@participant.cz||expression|//p||url|www.me.cz|',
			$participant
		);
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException The invitation is denied or does not exist
	 */
	public function testThrowingOnPrintingAffectedCode() {
		(new Subscribing\ParticipantInvitation(
			'abc',
			$this->database
		))->print(new Output\FakeFormat());
	}

	protected function prepareDatabase(): void {
		$this->purge(['participants', 'subscriptions', 'users', 'parts']);
	}
}

(new ParticipantInvitation)->run();