<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Response;

use Remembrall\Response;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class PermissionResponse extends Tester\TestCase {
	public function testPermissionWithoutXmlDeclaration() {
		Assert::notContains(
			'<?xml',
			(new Response\PermissionResponse())->body()->serialization()
		);
	}
}

(new PermissionResponse())->run();