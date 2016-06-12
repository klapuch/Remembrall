<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Subscribing;

use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class HtmlPart extends Tester\TestCase {
	public function testMergedHTMLNodes() {
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
				new Subscribing\FakePage,
				new Subscribing\FakeExpression(
					'//p',
					(new \DOMXPath($dom))->query('//p')
				)
			))->content()
		);
	}

	public function testEmptyNodes() {
		$dom = new \DOMDocument();
		$dom->loadHTML('<div><p>Blank</p></div>');
		Assert::same(
			'',
			(new Subscribing\HtmlPart(
				new Subscribing\FakePage,
				new Subscribing\FakeExpression(
					'//p',
					(new \DOMXPath($dom))->query('//span')
				)
			))->content()
		);
	}

	public function testSameContentButDifferentPage() {
		Assert::false(
			(new Subscribing\HtmlPart(
				new Subscribing\FakePage('google.com'),
				new Subscribing\FakeExpression()
			))->equals(
				new Subscribing\FakePart(
					'',
					new Subscribing\FakePage('seznam.cz')
				)
			)
		);
	}

	public function testDifferentContentButSamePage() {
		$dom = new \DOMDocument();
		$dom->loadHTML('<p>xxx</p>');
		Assert::false(
			(new Subscribing\HtmlPart(
				new Subscribing\FakePage('google.com'),
				new Subscribing\FakeExpression(
					'//p',
					(new \DOMXPath($dom))->query('//p')
				)
			))->equals(
				new Subscribing\FakePart(
					'<p>abc</p>',
					new Subscribing\FakePage('google.com')
				)
			)
		);
	}

	public function testEquivalentParts() {
		$dom = new \DOMDocument();
		$dom->loadHTML('<p>abc</p>');
		Assert::true(
			(new Subscribing\HtmlPart(
				new Subscribing\FakePage('google.com'),
				new Subscribing\FakeExpression(
					'//p',
					(new \DOMXPath($dom))->query('//p')
				)
			))->equals(
				new Subscribing\FakePart(
					'<p>abc</p>',
					new Subscribing\FakePage('google.com')
				)
			)
		);
	}
}

(new HtmlPart())->run();
