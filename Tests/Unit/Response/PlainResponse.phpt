<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Response;

use Klapuch\Output\FakeFormat;
use Remembrall\Response;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class PlainResponse extends Tester\TestCase {
	public function testNotChangedOutput() {
		Assert::equal(
			new FakeFormat('foo'),
			(new Response\PlainResponse(new FakeFormat('foo'), []))->body()
		);
		Assert::equal(
			['a' => 'b'],
			(new Response\PlainResponse(new FakeFormat(), ['a' => 'b']))->headers()
		);
	}
}

(new PlainResponse())->run();