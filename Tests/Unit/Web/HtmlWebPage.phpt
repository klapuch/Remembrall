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

	/**
	 * @throws \UnexpectedValueException Page must be available HTML page.
	 */
	public function testThrowingOnNonHtmlMediaType() {
		(new Web\HtmlWebPage(
			new Http\FakeRequest(
				new Http\FakeResponse(
					'body{}',
					['Content-Type' => 'text/css'],
					200
				)
			)
		))->content();
	}

	/**
	 * @throws \UnexpectedValueException Page must be available HTML page.
	 */
	public function testThrowingOnHttpError() {
		(new Web\HtmlWebPage(
			new Http\FakeRequest(
				new Http\FakeResponse(
					'Hi, there!',
					['Content-Type' => 'text/html'],
					404
				)
			)
		))->content();
	}

	public function testRefreshingGivingSamePage() {
		$page = new Web\HtmlWebPage(new Http\FakeRequest());
		Assert::equal($page, $page->refresh());
		Assert::notSame($page, $page->refresh());
	}
}

(new HtmlWebPage())->run();