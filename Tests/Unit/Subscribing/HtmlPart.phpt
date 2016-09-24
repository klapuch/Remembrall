<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Subscribing;

use Remembrall\Model\Subscribing;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class HtmlPart extends Tester\TestCase {
	public function testShrinkingHtml() {
		$dom = new \DOMDocument();
		$dom->loadHTML(
			'
			<div>
				<p>Hi<span>John</span>How are <span>you</span></p>
				<p><span>I am</span>thank<span>you</span></p>
				<p class="blank">Blank</p>
				<p>Invalid<p>
			</div>
			'
		);
		Assert::same(
			'<p>Hi<span>John</span>How are <span>you</span></p><p><span>I am</span>thank<span>you</span></p><p class="blank">Blank</p><p>Invalid</p><p></p>',
			(new Subscribing\HtmlPart(
				new Subscribing\FakeExpression(
					'//p',
					(new \DOMXPath($dom))->query('//p')
				),
				new Subscribing\FakePage()
			))->content()
		);
	}

	public function testEmptyPart() {
		$dom = new \DOMDocument();
		$dom->loadHTML('<div><p>Blank</p></div>');
		Assert::same(
			'',
			(new Subscribing\HtmlPart(
				new Subscribing\FakeExpression(
					'//invalid',
					(new \DOMXPath($dom))->query('//invalid')
				),
				new Subscribing\FakePage()
			))->content()
		);
	}

	public function testRefreshingWithPage() {
		$dom = new \DOMDocument();
		$dom->loadHTML('<p>XXX</p>');
		$refreshedPart = new Subscribing\FakePage($dom);
		$fakePage = new Subscribing\FakePage(null, $refreshedPart);
		Assert::equal(
			new Subscribing\HtmlPart(
				new Subscribing\FakeExpression('//p'),
				$refreshedPart
			),
			(new Subscribing\HtmlPart(
				new Subscribing\FakeExpression('//p'),
				$fakePage
			))->refresh()
		);
	}
}

(new HtmlPart())->run();
