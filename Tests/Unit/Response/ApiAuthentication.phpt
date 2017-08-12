<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 * @httpCode any
 */
namespace Remembrall\Unit\Response;

use Klapuch\Access;
use Klapuch\Output;
use Klapuch\Uri;
use Remembrall\Response;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ApiAuthentication extends Tester\TestCase {
	public function testAllowingAccess() {
		Assert::same(
			['foo' => 'bar'],
			(new Response\ApiAuthentication(
				new Response\PlainResponse(new Output\FakeFormat('foo'), ['foo' => 'bar']),
				new Access\FakeUser(1, ['role' => 'guest']),
				new Uri\FakeUri(null, 'v1/parts')
			))->headers()
		);
	}

	public function testProvidingDefaultRole() {
		Assert::same(
			['foo' => 'bar'],
			(new Response\ApiAuthentication(
				new Response\PlainResponse(new Output\FakeFormat('foo'), ['foo' => 'bar']),
				new Access\FakeUser(1, []),
				new Uri\FakeUri(null, 'v1/parts')
			))->headers()
		);
	}

	public function testForbiddenStatusCodeForDeniedAccess() {
		(new Response\ApiAuthentication(
			new Response\PlainResponse(new Output\FakeFormat('foo'), ['foo' => 'bar']),
			new Access\FakeUser(1, ['role' => 'guest']),
			new Uri\FakeUri('localhost', 'foo')
		))->headers();
		Assert::same(403, http_response_code());
	}

	public function testDefaultMessageOnForbiddenAccess() {
		Assert::same(
			'<?xml version="1.0" encoding="utf-8"?>
<message text="You are not allowed to see the response."/>
',
			(new Response\ApiAuthentication(
				new Response\PlainResponse(new Output\FakeFormat('foo'), ['foo' => 'bar']),
				new Access\FakeUser(1, ['role' => 'guest']),
				new Uri\FakeUri('localhost', 'foo')
			))->body()->serialization()
		);
	}
}

(new ApiAuthentication())->run();