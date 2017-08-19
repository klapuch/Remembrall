<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 * @httpCode any
 */
namespace Remembrall\Functional\V1\Subscription;

use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\TestCase;
use Remembrall\V1\Subscription;
use Tester\Assert;
use Tester\DomQuery;

require __DIR__ . '/../../../bootstrap.php';

final class Get extends \Tester\TestCase {
	use TestCase\Page;

	public function testWorkingRendering() {
		Assert::noError(function() {
			new \SimpleXMLElement(
				(new Subscription\Get(
					new Uri\FakeUri('', ''),
					new Log\FakeLogs(),
					new Ini\FakeSource($this->configuration)
				))->template(['id' => 1])->render()
			);
		});
	}

	public function testRenderingError() {
		$dom = DomQuery::fromXml(
			(new Subscription\Get(
				new Uri\FakeUri('', ''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->template(['id' => 2])->render()
		);
		Assert::same(
			'You can not see foreign subscription',
			(string) $dom->find('message')[0]->attributes()
		);
	}
}

(new Get())->run();