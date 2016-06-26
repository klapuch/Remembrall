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

final class CaseSensitiveHeader extends Tester\TestCase {
	public function testEqualsHeaders() {
		$header = new Http\CaseSensitiveHeader('connection', 'close');
		Assert::true($header->equals(new Http\FakeHeader('connection', 'close')));
		Assert::true($header->equals(new Http\FakeHeader('ConnectioN', 'close')));
		Assert::true($header->equals(new Http\FakeHeader('connection', 'ClosE')));
		Assert::true($header->equals(new Http\FakeHeader('CONNECTION', 'CLOSE')));
	}

	public function testDifferentHeaders() {
		$header = new Http\CaseSensitiveHeader('connection', 'close');
		Assert::false($header->equals(new Http\FakeHeader('server', 'nginx')));
		Assert::false($header->equals(new Http\FakeHeader('connection', 'keep-alive')));
	}
}

(new CaseSensitiveHeader())->run();
