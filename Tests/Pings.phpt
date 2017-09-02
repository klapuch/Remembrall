<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall;

use Klapuch\Http;
use Klapuch\Uri;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';

final class Pings extends \Tester\TestCase {
	private const BASE_URL = 'http://localhost:8888';

	/**
	 * @dataProvider guestNonInteractiveUrls
	 */
	public function testGuestNonInteractiveUrls(string $path, string $method): void {
		$response = (new Http\BasicRequest(
			$method,
			new Uri\FakeUri(sprintf('%s/%s', self::BASE_URL, $path))
		))->send();
		Assert::same(200, $response->code());
	}

	protected function guestNonInteractiveUrls(): array {
		return [
			// [path, method]
			['sign/in', 'GET'],
			['sign/up', 'GET'],
			['parts/popular', 'GET'],
			['parts/unreliable', 'GET'],
			['verification/request', 'GET'],
			['password/remind', 'GET'],
			['cron', 'GET'],
			['v1/parts?type=popular', 'GET'],
			['v1/parts?type=unreliable', 'GET'],
//			['verification/confirm/abc', 'GET'],
//			['invitation/accept/abc', 'GET'],
//			['invitation/decline/abc', 'GET'],
//			['password/reset/abc', 'GET'],
		];
	}
}

(new Pings())->run();