<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Web;

use Klapuch\Uri;
use Remembrall\Model\Web;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class SafeParts extends TestCase\Database {
	public function testThrowingOnLanguageOutOfAllowedEnum() {
		$ex = Assert::exception(function() {
			(new Web\SafeParts(
				new Web\CollectiveParts($this->database),
				$this->database
			))->add(
				new Web\FakePart('google content', null, 'google snap'),
				new Uri\FakeUri('www.google.com'),
				'//p',
				'foo'
			);
		}, \UnexpectedValueException::class, 'Allowed languages are "xpath, css" - "foo" given');
		Assert::type(\Throwable::class, $ex->getPrevious());
		$statement = $this->database->prepare('SELECT * FROM parts');
		$statement->execute();
		Assert::count(0, $statement->fetchAll());
	}

	public function testReThrowingUnknownError() {
		Assert::exception(function() {
			(new Web\SafeParts(
				new Web\FakeParts(new \PDOException('FOO')),
				$this->database
			))->add(
				new Web\FakePart('google content', null, 'google snap'),
				new Uri\FakeUri('www.google.com'),
				'//p',
				'foo'
			);
		}, \PDOException::class, 'FOO');
	}

	public function testDelegationToOriginWithoutAddError() {
		(new Web\SafeParts(
			new Web\CollectiveParts($this->database),
			$this->database
		))->add(
			new Web\FakePart('google content', null, 'google snap'),
			new Uri\FakeUri('www.google.com'),
			'//p',
			'xpath'
		);
		$statement = $this->database->prepare('SELECT * FROM parts');
		$statement->execute();
		Assert::count(1, $statement->fetchAll());
	}

	protected function prepareDatabase(): void {
		parent::prepareDatabase();
		$this->purge(['parts', 'subscriptions']);
	}
}

(new SafeParts)->run();