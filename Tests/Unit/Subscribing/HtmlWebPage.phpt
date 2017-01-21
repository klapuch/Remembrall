<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Subscribing;

use Klapuch\Http;
use Remembrall\Exception\NotFoundException;
use Remembrall\Model\Subscribing;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class HtmlWebPage extends Tester\TestCase {
	public function testNonProblematicHtml() {
		Assert::contains(
			'Hi, there!',
			(new Subscribing\HtmlWebPage(
				new Http\FakeRequest(
					new Http\FakeResponse(
						'Hi, there!',
						['Content-Type' => 'text/html'],
						200
					)
				)
			))->content()->saveHtml()
		);
	}

	public function testAllowingInvalidHtml() {
		Assert::contains(
			'Hi, there!',
			(new Subscribing\HtmlWebPage(
				new Http\FakeRequest(
					new Http\FakeResponse(
						'<a href="script.php?foo=bar&hello=world">Hi, there!</}>',
						['Content-Type' => 'text/html'],
						200
					)
				)
			))->content()->saveHtml()
		);
	}

	public function testThrowingOnNonHtmlMediaType() {
		$ex = Assert::exception(
			function() {
				(new Subscribing\HtmlWebPage(
					new Http\FakeRequest(
						new Http\FakeResponse(
							'body{}',
							['Content-Type' => 'text/css'],
							200
						)
					)
				))->content();
			},
			NotFoundException::class,
			'Page is unreachable. Does the URL exist?'
		);
		Assert::type(\Exception::class, $ex->getPrevious());
	}

	public function testThrowingOnHttpError() {
		$ex = Assert::exception(
			function() {
				(new Subscribing\HtmlWebPage(
					new Http\FakeRequest(
						new Http\FakeResponse(
							'Hi, there!',
							['Content-Type' => 'text/html'],
							404
						)
					)
				))->content();
			},
			NotFoundException::class,
			'Page is unreachable. Does the URL exist?'
		);
		Assert::type(\Exception::class, $ex->getPrevious());
	}

	public function testRefreshingGivingSamePage() {
		$page = new Subscribing\HtmlWebPage(new Http\FakeRequest());
		Assert::equal($page, $page->refresh());
		Assert::notSame($page, $page->refresh());
	}
}

(new HtmlWebPage())->run();