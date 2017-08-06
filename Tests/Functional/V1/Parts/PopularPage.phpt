<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\V1\Parts;

use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\TestCase;
use Remembrall\V1\Parts;
use Tester\Assert;
use Tester\DomQuery;

require __DIR__ . '/../../../bootstrap.php';

final class PopularPage extends \Tester\TestCase {
	use TestCase\Page;

	public function testWorkingRendering() {
		Assert::noError(function() {
			new \SimpleXMLElement(
				(new Parts\PopularPage(
					new Uri\FakeUri('', '/parts/popular'),
					new Log\FakeLogs(),
					new Ini\FakeSource($this->configuration)
				))->response([])->render()
			);
		});
	}

	public function testRenderingError() {
		$_GET['sort'] = 'foo';
		$dom = DomQuery::fromXml(
			(new Parts\PopularPage(
				new Uri\FakeUri('', '/parts/popular'),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response([])->render()
		);
		Assert::same(
			'Following criteria are not allowed: "foo"',
			(string) $dom->find('message')[0]->attributes()
		);
	}
}

(new PopularPage())->run();