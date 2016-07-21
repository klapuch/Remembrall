<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Http;

use GuzzleHttp;
use Remembrall\Model\Http;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class HtmlResponse extends TestCase\Mockery {
	/**
	 * @dataProvider validContentTypes
	 */
	public function testValidHtmlResponse($contentType) {
		$response = new Http\FakeResponse('abc');
		Assert::same(
			'abc',
			(new Http\HtmlResponse(
				$response,
				new GuzzleHttp\Psr7\Response(200, $contentType)
			))->content()
		);
	}

	/**
	 * @dataProvider invalidContentTypes
	 */
	public function testInvalidHtmlResponse($contentType) {
		Assert::exception(function() use($contentType) {
			$response = new Http\FakeResponse('abc');
			Assert::same(
				'abc',
				(new Http\HtmlResponse(
					$response,
					new GuzzleHttp\Psr7\Response(200, $contentType)
				))->content()
			);
		}, \Remembrall\Exception\NotFoundException::class, 'Response must be in HTML');
	}

	protected function validContentTypes() {
		return [
			[['Content-Type' => 'text/html; utf-8']],
			[['Content-Type' => 'Text/html; Utf-8']],
			[['Content-Type' => 'Text/html;']],
			[['Content-Type' => 'Text/html']],
		];
	}

	protected function invalidContentTypes() {
		return [
			[['Content-Type' => 'text/css']],
			[['Content-Type' => 'html']],
			[['Content-Type' => 'html/text']],
			[['Content-Type' => 'text/css;']],
			[['Content-Type' => 'utf-8']],
			[['Content-Type' => '']],
			[[]],
		];
	}
}

(new HtmlResponse())->run();
