<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Response;

use Klapuch\Application;
use Klapuch\Output;
use Remembrall\Response;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class InformativeResponse extends Tester\TestCase {
	public function testIncludedMessageForHeaders() {
		$sessions = [];
		$response = new Response\InformativeResponse(
			new class implements Application\Response {
				public function body(): Output\Format {
					return new Output\FakeFormat();
				}
				public function headers(): array {
					return [];
				}
			},
			['success' => 'It is fine'],
			$sessions
		);
		$response->body();
		Assert::same([], $sessions);
		$response->headers();
		Assert::contains([['success' => 'It is fine']], $sessions);
	}

	public function testMessageAfterSuccess() {
		$sessions = [];
		$response = new Response\InformativeResponse(
			new class implements Application\Response {
				public function body(): Output\Format {
				}
				public function headers(): array {
					throw new \DomainException('Fail');
				}
			},
			['success' => 'It is fine'],
			$sessions
		);
		Assert::exception(function() use ($response) {
			$response->headers();
		}, \DomainException::class);
		Assert::same([], $sessions);
	}

	public function testSingleMessage() {
		$sessions = [];
		$response = new Response\InformativeResponse(
			new class implements Application\Response {
				public function body(): Output\Format {
				}
				public function headers(): array {
					return [];
				}
			},
			['success' => 'It is fine', 'danger' => 'Oh, crap!'],
			$sessions
		);
		$response->headers();
		Assert::notContains([['danger' => 'Oh, crap!']], $sessions);
	}
}

(new InformativeResponse())->run();