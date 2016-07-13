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

final class CaseSensitiveHeaders extends Tester\TestCase {
	/**
	 * @throws \Remembrall\Exception\NotFoundException Header "wtf?" does not exist
	 */
	public function testUnknownHeader() {
		$headers = new Http\CaseSensitiveHeaders(
			new Http\FakeHeaders(['method' => 'get', 'Connection' => 'close'])
		);
		$headers->header('wtf?');
	}

	public function testCaseSensitiveHeader() {
		$headers = new Http\CaseSensitiveHeaders(
			new Http\FakeHeaders(['method' => 'get', 'Connection' => 'close'])
		);
		Assert::equal(
			new Http\FakeHeader('method', 'get'),
			$headers->header('mEtHoD')
		);
	}
}

(new CaseSensitiveHeaders())->run();
