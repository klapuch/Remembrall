<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Response;

use Klapuch\Output;
use Remembrall\Response;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class CombinedResponse extends Tester\TestCase {
	public function testCombinationOfMultipleResponsesInSameOrder() {
		Assert::equal(
			new Output\CombinedFormat(
				new Output\FakeFormat(),
				new Output\FakeFormat('foo')
			),
			(new Response\CombinedResponse(
				new Response\PlainResponse(new Output\FakeFormat()),
				new Response\PlainResponse(new Output\FakeFormat('foo'))
			))->body()
		);
	}

	public function testCombiningHeaders() {
		Assert::same(
			['a' => 'b', 'c' => 'd', 'C' => 'D'],
			(new Response\CombinedResponse(
				new Response\PlainResponse(new Output\FakeFormat(), ['a' => 'b']),
				new Response\PlainResponse(new Output\FakeFormat(), ['c' => 'd']),
				new Response\PlainResponse(new Output\FakeFormat(), ['C' => 'D'])
			))->headers()
		);
	}
}

(new CombinedResponse())->run();