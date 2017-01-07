<?php
/**
 * @testCase
 * @phpVersion > 7.1
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
		$expression = '//p';
		Assert::same(
			'<p>Hi<span>John</span>How are <span>you</span></p><p><span>I am</span>thank<span>you</span></p><p class="blank">Blank</p><p>Invalid</p><p></p>',
			(new Subscribing\HtmlPart(
				new Subscribing\FakeExpression(
					$expression,
					(new \DOMXPath($dom))->query($expression)
				),
				new Subscribing\FakePage()
			))->content()
		);
	}

	public function testAllowingEmptyPart() {
		$expression = '//invalid';
		$dom = new \DOMDocument();
		$dom->loadHTML('<div><p>Blank</p></div>');
		Assert::same(
			'',
			(new Subscribing\HtmlPart(
				new Subscribing\FakeExpression(
					$expression,
					(new \DOMXPath($dom))->query($expression)
				),
				new Subscribing\FakePage()
			))->content()
		);
	}

	public function testSnapshotHash() {
		$expression = '//p';
		$dom = new \DOMDocument();
		$dom->loadHTML('<div><p>SNAPSHOT</p></div>');
		Assert::same(
			sha1('<p>SNAPSHOT</p>'),
			(new Subscribing\HtmlPart(
				new Subscribing\FakeExpression(
					$expression,
					(new \DOMXPath($dom))->query($expression)
				),
				new Subscribing\FakePage()
			))->snapshot()
		);
	}

	public function testSnapshotLength() {
		$expression = '//p';
		$dom = new \DOMDocument();
		$dom->loadHTML('<div><p>Blank</p></div>');
		Assert::same(
			40,
			strlen(
				(new Subscribing\HtmlPart(
					new Subscribing\FakeExpression(
						$expression,
						(new \DOMXPath($dom))->query($expression)
					),
					new Subscribing\FakePage()
				))->snapshot()
			)
		);
	}

	public function testRefreshingWithPage() {
		$expression = '//p';
		$dom = new \DOMDocument();
		$dom->loadHTML('<p>XXX</p>');
		$refreshedPart = new Subscribing\FakePage($dom);
		Assert::equal(
			new Subscribing\HtmlPart(
				new Subscribing\FakeExpression($expression),
				$refreshedPart
			),
			(new Subscribing\HtmlPart(
				new Subscribing\FakeExpression($expression),
				new Subscribing\FakePage(null, $refreshedPart)
			))->refresh()
		);
	}
}

(new HtmlPart())->run();