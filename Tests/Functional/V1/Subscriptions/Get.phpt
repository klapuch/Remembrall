<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 * @httpCode any
 */
namespace Remembrall\Functional\V1\Subscriptions;

use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Misc;
use Remembrall\TestCase;
use Remembrall\V1\Subscriptions;
use Tester\Assert;
use Tester\DomQuery;

require __DIR__ . '/../../../bootstrap.php';

final class Get extends \Tester\TestCase {
	use TestCase\Page;

	public function testWorkingRendering() {
		$user = (new Misc\ApiTestUsers($this->database))->register();
		(new Misc\SamplePart($this->database))->try();
		(new Misc\SamplePart($this->database))->try();
		(new Misc\SampleSubscription($this->database, $user, 1))->try();
		(new Misc\SampleSubscription($this->database, $user, 2))->try();
		$dom = DomQuery::fromXml(
			(new Subscriptions\Get(
				new Uri\FakeUri('', 'v1/subscriptions'),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->template([])->render()
		);
		Assert::same(200, http_response_code());
		Assert::true($dom->has('subscriptions'));
		Assert::true($dom->has('subscription'));
		Assert::true($dom->has('id'));
	}

	public function testRenderingError() {
		$_GET['sort'] = 'foo';
		$dom = DomQuery::fromXml(
			(new Subscriptions\Get(
				new Uri\FakeUri('', ''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->template([])->render()
		);
		Assert::same(400, http_response_code());
		Assert::same(
			'Following criteria are not allowed: "foo"',
			(string) $dom->find('message')[0]->attributes()
		);
	}
}

(new Get())->run();