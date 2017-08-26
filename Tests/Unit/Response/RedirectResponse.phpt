<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Response;

use Klapuch\Output;
use Klapuch\Uri;
use Remembrall\Response;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class RedirectResponse extends Tester\TestCase {
	public function testRewritingHeaderLocation() {
		Assert::same(
			['Location' => 'http://site.com/sign/in', 'Content-Type' => 'bar'],
			(new Response\RedirectResponse(
				new Response\PlainResponse(
					new Output\FakeFormat('foo'),
					['Location' => 'wtf', 'Content-Type' => 'bar']
				),
				new Uri\FakeUri('http://site.com', 'sign/in')
			))->headers()
		);
	}
}

(new RedirectResponse())->run();