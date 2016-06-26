<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Http;

use GuzzleHttp;
use Remembrall\Model\Http;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class DefaultResponse extends Tester\TestCase {
	public function testContent() {
		$http = new GuzzleHttp\Client();
		Assert::contains(
			'<!DOCTYPE html>',
			(new Http\DefaultResponse(
				$http->request('GET', 'https://nette.org/')
			))->content()
		);
	}

	public function testHeaders() {
		$http = new GuzzleHttp\Client();
		$headers = (new Http\DefaultResponse(
			$http->request('GET', 'https://nette.org/')
		))->headers();
		Assert::same('text/html; charset=utf-8', $headers->header('Content-Type')->value());
	}

	public function testAdditionalHeaders() {
		$http = new GuzzleHttp\Client();
		$headers = (new Http\DefaultResponse(
			$http->request('GET', 'https://nette.org/')
		))->headers();
		Assert::same('200 OK', $headers->header('Status')->value());
		Assert::same('1.1', $headers->header('Protocol')->value());
	}
}

(new DefaultResponse())->run();
