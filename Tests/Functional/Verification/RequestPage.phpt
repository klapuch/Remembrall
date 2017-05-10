<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Verification;

use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Page\Verification;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class RequestPage extends TestCase\Page {
	public function testWorkingResponse() {
		Assert::noError(function() {
			$body = (new Verification\RequestPage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response([])->body()->serialization();
			$dom = new \DOMDocument();
			$dom->loadXML($body);
		});
	}
}

(new RequestPage())->run();