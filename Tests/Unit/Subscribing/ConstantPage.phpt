<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Http;

use Remembrall\Model\{
	Http, Subscribing
};
use Remembrall\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ConstantPage extends TestCase\Mockery {
	public function testEquivalentPages() {
		Assert::true(
			(new Subscribing\ConstantPage(
				'', ''
			))->equals(new Subscribing\FakePage(null, null, $equals = true))
		);
	}

	public function testDifferentPages() {
		Assert::false(
			(new Subscribing\ConstantPage(
				'', ''
			))->equals(new Subscribing\FakePage(null, null, $equals = false))
		);
	}
}

(new ConstantPage())->run();
