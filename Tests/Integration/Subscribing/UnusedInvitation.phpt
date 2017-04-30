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

final class UnusedInvitation extends TestCase\Database {
	public function testThrowingOnUnknownCode() {
		Assert::exception(function() {
			(new Subscribing\UnusedInvitation(
				new Subscribing\FakeInvitation(),
				'abcd',
				$this->database
			))->accept();
		}, \Remembrall\Exception\NotFoundException::class, 'The invitation is accepted or does not exist');
		Assert::exception(function() {
			(new Subscribing\UnusedInvitation(
				new Subscribing\FakeInvitation(),
				'abcd',
				$this->database
			))->deny();
		}, \Remembrall\Exception\NotFoundException::class, 'The invitation is denied or does not exist');
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
		}, \Remembrall\Exception\NotFoundException::class, 'The invitation is accepted or does not exist');
		Assert::exception(function() {
			(new Subscribing\UnusedInvitation(
				new Subscribing\FakeInvitation(),
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
		(new Subscribing\UnusedInvitation(
			new Subscribing\FakeInvitation(),
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
		(new Subscribing\UnusedInvitation(
			new Subscribing\FakeInvitation(),
			strtoupper($code),
			$this->database
		))->accept();
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
		(new Subscribing\UnusedInvitation(
			new Subscribing\FakeInvitation(),
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
		(new Subscribing\UnusedInvitation(
			new Subscribing\FakeInvitation(),
			$code,
			$this->database
		))->deny();
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException The invitation is denied or does not exist
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

	public function testDenying() {
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
			))->deny();
		});
	}

	protected function prepareDatabase(): void {
		$this->purge(['participants']);
	}
}

(new UnusedInvitation)->run();