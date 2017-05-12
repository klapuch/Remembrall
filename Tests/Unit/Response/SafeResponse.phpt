<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Response;

use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;
use Remembrall\Response;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class SafeResponse extends Tester\TestCase {
	/**
	 * @throws \DomainException foo
	 */
	public function testHeadersWithLeakedError() {
		$sessions = [];
		(new Response\SafeResponse(
			new class implements Application\Response {
				public function body(): Output\Format {
				}
				public function headers(): array {
					throw new \DomainException('foo');
				}
			},
			new Uri\FakeUri('', ''),
			$sessions
		))->headers();
	}

	public function testRedirectOnBodyError() {
		$sessions = [];
		$headers = (new Response\SafeResponse(
			new class implements Application\Response {
				public function body(): Output\Format {
					throw new \DomainException('foo');
				}
				public function headers(): array {
					return ['Location' => 'foo', 'Content-Type' => 'html'];
				}
			},
			new Uri\FakeUri('base.cz', 'sign/in'),
			$sessions
		))->headers();
		Assert::notSame([], $sessions);
		Assert::same(['Location' => 'base.cz/sign/in', 'Content-Type' => 'html'], $headers);
	}

	public function testPassingWithDefaults() {
		$sessions = [];
		$response = new Response\SafeResponse(
			new class implements Application\Response {
				public function body(): Output\Format {
					return new Output\FakeFormat();
				}
				public function headers(): array {
					return ['Location' => 'foo', 'Content-Type' => 'html'];
				}
			},
			new Uri\FakeUri('', 'sign/in'),
			$sessions
		);
		Assert::equal(new Output\FakeFormat(), $response->body());
		Assert::same([], $sessions);
		Assert::same(['Location' => 'foo', 'Content-Type' => 'html'], $response->headers());
	}
}

(new SafeResponse())->run();