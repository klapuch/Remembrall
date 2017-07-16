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

final class UnusedInvitation extends \Tester\TestCase {
	use TestCase\Database;

	public function testThrowingOnUnknownCode() {
		Assert::exception(function() {
			(new Subscribing\UnusedInvitation(
				new Subscribing\FakeInvitation(),
				'abcd',
				$this->database
			))->accept();
		}, \UnexpectedValueException::class, 'The invitation with code "abcd" is accepted or does not exist');
		Assert::exception(function() {
			(new Subscribing\UnusedInvitation(
				new Subscribing\FakeInvitation(),
				'abcd',
				$this->database
			))->decline();
		}, \UnexpectedValueException::class, 'The invitation with code "abcd" is declined or does not exist');
		$this->database->prepare(
			"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			('me@participant.cz', 1, 'abc', NOW(), FALSE, NULL)"
		)->execute();
		Assert::exception(function() {
			(new Subscribing\UnusedInvitation(
				new Subscribing\FakeInvitation(),
				'abcd',
				$this->database
			))->accept();
		}, \UnexpectedValueException::class, 'The invitation with code "abcd" is accepted or does not exist');
		Assert::exception(function() {
			(new Subscribing\UnusedInvitation(
				new Subscribing\FakeInvitation(),
				'abcd',
				$this->database
			))->decline();
		}, \UnexpectedValueException::class, 'The invitation with code "abcd" is declined or does not exist');
	}

	/**
	 * @throws \UnexpectedValueException The invitation with code "abc" is accepted or does not exist
	 */
	public function testThrowingOnAcceptingAlreadyAcceptedCode() {
		$code = 'abc';
		$this->database->prepare(
			"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			('me@participant.cz', 1, ?, NOW(), TRUE, NULL)"
		)->execute([$code]);
		(new Subscribing\UnusedInvitation(
			new Subscribing\FakeInvitation(),
			$code,
			$this->database
		))->accept();
	}

	/**
	 * @throws \UnexpectedValueException The invitation with code "ABC" is accepted or does not exist
	 */
	public function testThrowingOnAcceptingCaseInsensitiveCode() {
		$code = 'abc';
		$this->database->prepare(
			"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			('me@participant.cz', 1, ?, NOW(), FALSE, NULL)"
		)->execute([$code]);
		(new Subscribing\UnusedInvitation(
			new Subscribing\FakeInvitation(),
			strtoupper($code),
			$this->database
		))->accept();
	}

	/**
	 * @throws \UnexpectedValueException The invitation with code "ABC" is declined or does not exist
	 */
	public function testThrowingOnDecliningCaseInsensitiveCode() {
		$code = 'abc';
		$this->database->prepare(
			"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			('me@participant.cz', 1, ?, NOW(), FALSE, NULL)"
		)->execute([$code]);
		(new Subscribing\UnusedInvitation(
			new Subscribing\FakeInvitation(),
			strtoupper($code),
			$this->database
		))->decline();
	}

	/**
	 * @throws \UnexpectedValueException The invitation with code "abc" is declined or does not exist
	 */
	public function testThrowingOnDeclineAlreadyDeclinedCode() {
		$code = 'abc';
		$this->database->prepare(
			"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			('me@participant.cz', 1, ?, NOW(), FALSE, NOW())"
		)->execute([$code]);
		(new Subscribing\UnusedInvitation(
			new Subscribing\FakeInvitation(),
			$code,
			$this->database
		))->decline();
	}

	/**
	 * @throws \UnexpectedValueException The invitation with code "abc" is declined or does not exist
	 */
	public function testThrowingOnPrintingAffectedCode() {
		(new Subscribing\UnusedInvitation(
			new Subscribing\FakeInvitation(),
			'abc',
			$this->database
		))->print(new Output\FakeFormat());
	}

	public function testPrinting() {
		$code = 'abc';
		$this->database->prepare(
			"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			('me@participant.cz', 1, ?, NOW(), FALSE, NULL)"
		)->execute([$code]);
		Assert::equal(
			new Output\FakeFormat(),
			(new Subscribing\UnusedInvitation(
				new Subscribing\FakeInvitation(),
				$code,
				$this->database
			))->print(new Output\FakeFormat())
		);
	}

	public function testAccepting() {
		$code = 'abc';
		$this->database->prepare(
			"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			('me@participant.cz', 1, ?, NOW(), FALSE, NULL)"
		)->execute([$code]);
		Assert::noError(function() use ($code) {
			(new Subscribing\UnusedInvitation(
				new Subscribing\FakeInvitation(),
				$code,
				$this->database
			))->accept();
		});
	}

	public function testDeclining() {
		$code = 'abc';
		$this->database->prepare(
			"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			('me@participant.cz', 1, ?, NOW(), FALSE, NULL)"
		)->execute([$code]);
		Assert::noError(function() use ($code) {
			(new Subscribing\UnusedInvitation(
				new Subscribing\FakeInvitation(),
				$code,
				$this->database
			))->decline();
		});
	}
}

(new UnusedInvitation)->run();