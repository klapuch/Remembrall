<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Web;

use Klapuch\Http;
use Remembrall\Model\Web;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class HtmlWebPage extends Tester\TestCase {
	public function testNonProblematicHtml() {
		Assert::contains(
			'Hi, there!',
			(new Web\HtmlWebPage(
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
			(new Web\HtmlWebPage(
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
				(new Web\HtmlWebPage(
					new Http\FakeRequest(
						new Http\FakeResponse(
							'body{}',
							['Content-Type' => 'text/css'],
							200
						)
					)
				))->content();
			},
			\UnexpectedValueException::class,
			'Page is unreachable. Does the URL exist?'
		);
		Assert::type(\Throwable::class, $ex->getPrevious());
	}

	public function testThrowingOnHttpError() {
		$ex = Assert::exception(
			function() {
				(new Web\HtmlWebPage(
					new Http\FakeRequest(
						new Http\FakeResponse(
							'Hi, there!',
							['Content-Type' => 'text/html'],
							404
						)
					)
				))->content();
			},
			\UnexpectedValueException::class,
			'Page is unreachable. Does the URL exist?'
		);
		Assert::type(\Throwable::class, $ex->getPrevious());
	}

	public function testRefreshingGivingSamePage() {
		$page = new Web\HtmlWebPage(new Http\FakeRequest());
		Assert::equal($page, $page->refresh());
		Assert::notSame($page, $page->refresh());
	}
}

(new HtmlWebPage())->run();