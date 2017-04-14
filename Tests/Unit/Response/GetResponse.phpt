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

final class GetResponse extends Tester\TestCase {
	public function testGetArrayInXml() {
		$_GET['name'] = 'Dom';
		$_GET['age'] = 20;
		Assert::same(
			'<request><get><name>Dom</name><age>20</age></get></request>',
			(new Response\GetResponse())->body()->serialization()
		);
	}
}

(new GetResponse())->run();