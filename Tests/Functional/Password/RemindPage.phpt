<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Password;

use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Page\Password;
use Remembrall\TestCase;
use Tester\Assert;
use Tester\DomQuery;

require __DIR__ . '/../../bootstrap.php';

final class RemindPage extends \Tester\TestCase {
	use TestCase\Page;

	public function testWorkingRendering() {
		Assert::same(
			'Remind password',
			(string) DomQuery::fromHtml(
				(new Password\RemindPage(
					new Uri\FakeUri('', '/password/remind'),
					new Log\FakeLogs(),
					new Ini\FakeSource($this->configuration)
				))->response([])->render(['nonce' => '', 'base_url' => ''])
			)->find('h1')[0]
		);
	}
}

(new RemindPage())->run();