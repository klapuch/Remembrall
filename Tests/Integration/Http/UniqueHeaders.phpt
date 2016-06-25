<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Http;

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
				'method' => new Http\ConstantHeader('method', 'get'),
				'Content-Type' => new Http\ConstantHeader('Content-Type', 'text/html; utf-8'),
			],
			$headers->iterate()
		);
	}

	public function testObjectHeaders() {
		$headers = new Http\UniqueHeaders(
			[
				new Http\ConstantHeader('method', 'post'),
				new Http\ConstantHeader('Connection', 'close'),
			]
		);
		Assert::equal(
			[
				'method' => new Http\ConstantHeader('method', 'post'),
				'Connection' => new Http\ConstantHeader('Connection', 'close'),
			],
			$headers->iterate()
		);
	}

	public function testTypeMixedHeaders() {
		$headers = new Http\UniqueHeaders(
			[
				'method' => 'post',
				'Connection' => 'close',
				new Http\ConstantHeader('Content-Type', 'text/html; utf-8'),
			]
		);
		Assert::equal(
			[
				'method' => new Http\ConstantHeader('method', 'post'),
				'Connection' => new Http\ConstantHeader('Connection', 'close'),
				'Content-Type' => new Http\ConstantHeader('Content-Type', 'text/html; utf-8'),
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
				'method' => new Http\ConstantHeader('method', 'post'),
				'Connection' => new Http\ConstantHeader('Connection', 'close'),
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
}

(new UniqueHeaders())->run();
