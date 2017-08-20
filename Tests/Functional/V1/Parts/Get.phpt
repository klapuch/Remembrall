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
		$this->database->exec(
			"INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES
			(1, 'foo.cz', ROW('//foo', 'xpath'), 'foo', 'fooSnap')"
		);
		$this->database->exec(
			"INSERT INTO subscriptions (user_id, part_id, interval, last_update, snapshot) VALUES
			(1, 1, 'PT6M', NOW(), md5(random()::text))"
		);
		$_GET['type'] = 'popular';
		$dom = DomQuery::fromXml(
			(new Parts\Get(
				new Uri\FakeUri('', 'v1/parts'),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->template([])->render()
		);
		Assert::same(200, http_response_code());
		Assert::true($dom->has('parts'));
		Assert::true($dom->has('part'));
		Assert::true($dom->has('id'));
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
		Assert::same(400, http_response_code());
		Assert::same(
			'Following criteria are not allowed: "foo"',
			(string) $dom->find('message')[0]->attributes()
		);
	}
}

(new Get())->run();