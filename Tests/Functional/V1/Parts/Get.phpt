<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 * @httpCode any
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

final class Get extends \Tester\TestCase {
	use TestCase\Page;

	public function testWorkingRendering() {
		$_GET['type'] = 'popular';
		Assert::noError(function() {
			new \SimpleXMLElement(
				(new Parts\Get(
					new Uri\FakeUri('', ''),
					new Log\FakeLogs(),
					new Ini\FakeSource($this->configuration)
				))->template([])->render()
			);
		});
	}

	public function testRenderingError() {
		$_GET['sort'] = 'foo';
		$_GET['type'] = 'popular';
		$dom = DomQuery::fromXml(
			(new Parts\Get(
				new Uri\FakeUri('', ''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->template([])->render()
		);
		Assert::same(
			'Following criteria are not allowed: "foo"',
			(string) $dom->find('message')[0]->attributes()
		);
	}
}

(new Get())->run();