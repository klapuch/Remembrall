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
		(new Misc\SampleParticipant($this->database))->try();
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
		(new Misc\SampleParticipant(
			$this->database,
			['code' => $code, 'accepted' => true]
		))->try();
		(new Subscribing\UnusedInvitation(
			new Subscribing\FakeInvitation(),
			$code,
			$this->database
		))->accept();
	}

	/**
	 * @throws \UnexpectedValueException The invitation with code "ABC" is accepted or does not exist
	 */
	public function testThrowingOnAcceptingAlreadyAcceptedCaseInsensitiveCode() {
		$code = 'abc';
		(new Misc\SampleParticipant(
			$this->database,
			['code' => $code, 'accepted' => false]
		))->try();
		(new Subscribing\UnusedInvitation(
			new Subscribing\FakeInvitation(),
			strtoupper($code),
			$this->database
		))->accept();
	}

	/**
	 * @throws \UnexpectedValueException The invitation with code "abc" is declined or does not exist
	 */
	public function testThrowingOnDeclineAlreadyDeclinedCode() {
		$code = 'abc';
		(new Misc\SampleParticipant(
			$this->database,
			['code' => $code, 'accepted' => false]
		))->try();
		(new Subscribing\UnusedInvitation(
			new Subscribing\FakeInvitation(),
			$code,
			$this->database
		))->decline();
	}

	/**
	 * @throws \UnexpectedValueException The invitation with code "ABC" is declined or does not exist
	 */
	public function testThrowingOnDecliningCaseInsensitiveCode() {
		$code = 'abc';
		(new Misc\SampleParticipant(
			$this->database,
			['code' => $code, 'accepted' => false]
		))->try();
		(new Subscribing\UnusedInvitation(
			new Subscribing\FakeInvitation(),
			strtoupper($code),
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
		(new Misc\SampleParticipant(
			$this->database,
			['code' => $code, 'accepted' => true, 'decided_at' => 'NULL']
		))->try();
		Assert::equal(
			new Output\FakeFormat(),
			(new Subscribing\UnusedInvitation(
				new Subscribing\FakeInvitation(),
				$code,
				$this->database
			))->print(new Output\FakeFormat())
		);
	}

	public function testPassingOnAccepting() {
		$code = 'abc';
		(new Misc\SampleParticipant(
			$this->database,
			['code' => $code, 'accepted' => false, 'decided_at' => 'NULL']
		))->try();
		Assert::noError(function() use ($code) {
			(new Subscribing\UnusedInvitation(
				new Subscribing\FakeInvitation(),
				$code,
				$this->database
			))->accept();
		});
	}

	public function testPassingOnDeclining() {
		$code = 'abc';
		(new Misc\SampleParticipant(
			$this->database,
			['code' => $code, 'decided_at' => 'NULL']
		))->try();
		Assert::noError(
			function() use ($code) {
				(new Subscribing\UnusedInvitation(
					new Subscribing\FakeInvitation(),
					$code,
					$this->database
				))->decline();
			}
		);
	}
}

(new UnusedInvitation)->run();