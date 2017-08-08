<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Web;

use Remembrall\Model\Web;
use Remembrall\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class SuitedParts extends Tester\TestCase {
	use TestCase\Database;

	public function testPassingWithAllowedTypes() {
		Assert::noError(function() {
			(new Web\SuitedParts('popular', $this->database))->count();
			(new Web\SuitedParts('unreliable', $this->database))->count();
		});
	}

	public function testThrowingOnUnknownType() {
		Assert::exception(function() {
			(new Web\SuitedParts('foo', $this->database))->count();
		}, \UnexpectedValueException::class, 'Allowed types are popular, unreliable');
	}

	public function testPassingWithCaseInsensitiveOnes() {
		Assert::noError(function() {
			(new Web\SuitedParts('poPulaR', $this->database))->count();
			(new Web\SuitedParts('UnReliAble', $this->database))->count();
		});
	}
}

(new SuitedParts())->run();