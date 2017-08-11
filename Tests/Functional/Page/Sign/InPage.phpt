<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Page\Sign;

use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Misc;
use Remembrall\Page\Sign;
use Remembrall\TestCase;
use Tester\Assert;
use Tester\DomQuery;

require __DIR__ . '/../../../bootstrap.php';

final class InPage extends \Tester\TestCase {
	use TestCase\Page;

	public function testWorkingRendering() {
		Assert::same(
			'Sign in',
			(string) DomQuery::fromHtml(
				(new Misc\TestTemplate(
					(new Sign\InPage(
						new Uri\FakeUri('', '/sign/in'),
						new Log\FakeLogs(),
						new Ini\FakeSource($this->configuration)
					))->template([])
				))->render()
			)->find('h1')[0]
		);
	}
}

(new InPage())->run();