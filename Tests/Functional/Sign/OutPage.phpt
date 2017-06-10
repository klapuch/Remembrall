<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Sign;

use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Page\Sign;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class OutPage extends \Tester\TestCase {
	use TestCase\Page;

	public function testWorkingResponse() {
		$body = (new Sign\OutPage(
			new Uri\FakeUri(''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->response([])->body()->serialization();
		Assert::same('', $body);
	}

	public function testRedirectionInEveryCase() {
		$headers = (new Sign\OutPage(
			new Uri\FakeUri(''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->response([])->headers();
		Assert::same(['Location' => '/sign/in'], $headers);
	}
}

(new OutPage())->run();