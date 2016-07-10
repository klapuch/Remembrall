<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Http;

use GuzzleHttp;
use Remembrall\Model\Http;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class UniqueHeaders extends Tester\TestCase {
	public function testAssociativeArrayHeaders() {
		$headers = new Http\UniqueHeaders(
			['method' => 'get', 'Content-Type' => 'text/html; utf-8']
		);
		Assert::equal(
			[
				'method' => new Http\CaseSensitiveHeader('method', 'get'),
				'Content-Type' => new Http\CaseSensitiveHeader('Content-Type', 'text/html; utf-8'),
			],
			$headers->iterate()
		);
	}

	public function testConvertedTypesToString() {
		$headers = new Http\UniqueHeaders(
			['something' => 1, 'else' => 0, 'http_errors' => false, 'allow_redirect' => true, 'array' => []]
		);
		Assert::equal(
			[
				'something' => new Http\CaseSensitiveHeader('something', '1'),
				'else' => new Http\CaseSensitiveHeader('else', '0'),
				'http_errors' => new Http\CaseSensitiveHeader('http_errors', ''),
				'allow_redirect' => new Http\CaseSensitiveHeader('allow_redirect', '1'),
			],
			$headers->iterate()
		);
	}

	public function testObjectHeaders() {
		$headers = new Http\UniqueHeaders(
			[
				new Http\FakeHeader('method', 'post'),
				new Http\FakeHeader('Connection', 'close'),
			]
		);
		Assert::equal(
			[
				'method' => new Http\FakeHeader('method', 'post'),
				'Connection' => new Http\FakeHeader('Connection', 'close'),
			],
			$headers->iterate()
		);
	}

	public function testTypeMixedHeaders() {
		$headers = new Http\UniqueHeaders(
			[
				'method' => 'post',
				'Connection' => 'close',
				new Http\FakeHeader('Content-Type', 'text/html; utf-8'),
			]
		);
		Assert::equal(
			[
				'method' => new Http\CaseSensitiveHeader('method', 'post'),
				'Connection' => new Http\CaseSensitiveHeader('Connection', 'close'),
				'Content-Type' => new Http\FakeHeader('Content-Type', 'text/html; utf-8'),
			],
			$headers->iterate()
		);
	}

	public function testUniqueness() {
		$headers = new Http\UniqueHeaders(
			['method' => 'get', 'method' => 'post', 'Connection' => 'close']
		);
		Assert::equal(
			[
				'method' => new Http\CaseSensitiveHeader('method', 'post'),
				'Connection' => new Http\CaseSensitiveHeader('Connection', 'close'),
			],
			$headers->iterate()
		);
	}

	/**
	 * @throws \Remembrall\Exception\ExistenceException Header "wtf?" does not exist
	 */
	public function testUnknownHeader() {
		$headers = new Http\UniqueHeaders(
			['method' => 'get', 'Connection' => 'close']
		);
		$headers->header('wtf?');
	}

	public function testIncludedHeader() {
		$headers = new Http\UniqueHeaders(
			[
				new Http\FakeHeader('method', 'get', true),
				new Http\FakeHeader('Connection', 'close', true),
			]
		);
		Assert::true($headers->included(new Http\FakeHeader('method', 'get')));
	}

	public function testNotIncludedHeader() {
		$headers = new Http\UniqueHeaders(
			[
				new Http\FakeHeader('method', 'get', false),
				new Http\FakeHeader('Connection', 'close', true),
			]
		);
		Assert::true($headers->included(new Http\FakeHeader('method', 'get')));
	}

	public function testConversionToArray() {
		$headers = new Http\UniqueHeaders(
			[
				new Http\FakeHeader('method', 'get'),
				new Http\FakeHeader('Connection', 'close'),
				new Http\FakeHeader('Connection', 'close'),
			]
		);
		Assert::same(
			['method' => 'get', 'Connection' => 'close'],
			$headers->toArray()
		);
	}
}

(new UniqueHeaders())->run();
