<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Page\Subscription;

use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Misc;
use Remembrall\Page\Subscription;
use Remembrall\TestCase;
use Tester\Assert;
use Tester\DomQuery;

require __DIR__ . '/../../../bootstrap.php';

final class DefaultPage extends \Tester\TestCase {
	use TestCase\Page;

	public function testWorkingRendering() {
		Assert::same(
			'Subscription',
			(string) DomQuery::fromHtml(
				(new Misc\TestTemplate(
					(new Subscription\DefaultPage(
						new Uri\FakeUri('', '/sign/in'),
						new Log\FakeLogs(),
						new Ini\FakeSource($this->configuration)
					))->response([])
				))->render()
			)->find('h1')[0]
		);
	}
}

(new DefaultPage())->run();