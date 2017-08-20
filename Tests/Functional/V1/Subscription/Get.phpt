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
use Remembrall\Misc;
use Remembrall\TestCase;
use Remembrall\V1\Subscription;
use Tester\Assert;
use Tester\DomQuery;

require __DIR__ . '/../../../bootstrap.php';

final class Get extends \Tester\TestCase {
	use TestCase\Page;

	public function testWorkingRendering() {
		$user = (new Misc\ApiTestUsers($this->database))->register();
		$this->database->exec(
			"INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES
			(2, 'https://www.google.com', ROW('//a', 'xpath'), 'a', '')"
		);
		$this->database->exec(
			"INSERT INTO subscriptions (id, part_id, user_id, interval, last_update, snapshot) VALUES
			(1, 2, '{$user->id()}', 'PT1M', '1993-01-01', '')"
		);
		$dom = DomQuery::fromXml(
			(new Subscription\Get(
				new Uri\FakeUri('', 'v1/subscriptions/1'),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->template(['id' => 1])->render()
		);
		Assert::same(200, http_response_code());
		Assert::true($dom->has('subscription'));
		Assert::true($dom->has('id'));
	}

	public function testRenderingErrorForForeignAccess() {
		$dom = DomQuery::fromXml(
			(new Subscription\Get(
				new Uri\FakeUri('', ''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->template(['id' => 2])->render()
		);
		Assert::same(403, http_response_code());
		Assert::same(
			'You can not see foreign subscription',
			(string) $dom->find('message')[0]->attributes()
		);
	}
}

(new Get())->run();