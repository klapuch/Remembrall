<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Subscribing;

use Remembrall\Model\{
	Http, Subscribing
};
use Remembrall\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class AvailableWebPage extends TestCase\Mockery {
	/**
	 * @throws \Remembrall\Exception\ExistenceException Web page "www.foo.xxx" can not be loaded because of 404 Not Found
	 */
	public function testNotFoundPage() {
		(new Subscribing\AvailableWebPage(
			new Subscribing\FakePage('www.foo.xxx'),
			new Http\FakeResponse(
				new Http\FakeHeaders(['Status' => '404 Not Found']), ''
			)
		))->content();
	}

	public function testFoundPage() {
		Assert::noError(function() {
			(new Subscribing\AvailableWebPage(
				new Subscribing\FakePage('www.foo.xxx', new \DOMDocument()),
				new Http\FakeResponse(
					new Http\FakeHeaders(['Status' => '200 OK']), ''
				)
			))->content();
		});
	}
}

(new AvailableWebPage())->run();
