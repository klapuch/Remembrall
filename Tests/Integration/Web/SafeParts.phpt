<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Web;

use Klapuch\Uri;
use Remembrall\Misc;
use Remembrall\Model\Web;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class SafeParts extends \Tester\TestCase {
	use TestCase\Database;

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
		(new Misc\TableCount($this->database, 'parts', 0))->assert();
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
		(new Misc\TableCount($this->database, 'parts', 1))->assert();
	}
}

(new SafeParts)->run();