<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Page\Verification;

use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Misc;
use Remembrall\Page\Verification;
use Remembrall\TestCase;
use Tester\Assert;
use Tester\DomQuery;

require __DIR__ . '/../../../bootstrap.php';

final class RequestPage extends \Tester\TestCase {
	use TestCase\Page;

	public function testWorkingRendering() {
		Assert::same(
			'Verification request',
			(string) DomQuery::fromHtml(
				(new Misc\TestTemplate(
					(new Verification\RequestPage(
						new Uri\FakeUri('', '/verification/request'),
						new Log\FakeLogs(),
						new Ini\FakeSource($this->configuration)
					))->response([])
				))->render()
			)->find('h1')[0]
		);
	}
}

(new RequestPage())->run();