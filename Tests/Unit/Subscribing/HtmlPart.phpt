<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Subscribing;

use Remembrall\Model\{
	Subscribing, Access
};
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
				),
				new Access\FakeSubscriber()
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
				),
				new Access\FakeSubscriber()
			))->content()
		);
	}

	public function testSameContentButDifferentPage() {
		Assert::false(
			(new Subscribing\HtmlPart(
				new Subscribing\FakePage('google.com'),
				new Subscribing\FakeExpression(),
				new Access\FakeSubscriber()
			))->equals(
				new Subscribing\FakePart(
					new Subscribing\FakePage('seznam.cz'),
					''
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
				),
				new Access\FakeSubscriber()
			))->equals(
				new Subscribing\FakePart(
					new Subscribing\FakePage('google.com'),
					'<p>abc</p>'
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
				),
				new Access\FakeSubscriber()
			))->equals(
				new Subscribing\FakePart(
					new Subscribing\FakePage('google.com'),
					'<p>abc</p>'
				)
			)
		);
	}

	public function testVisitedAtWithoutError() {
		$dom = new \DOMDocument();
		$dom->loadHTML('<p>abc</p>');
		Assert::type(
			Subscribing\DateTimeInterval::class,
			(new Subscribing\HtmlPart(
				new Subscribing\FakePage('google.com'),
				new Subscribing\FakeExpression(
					'//p',
					(new \DOMXPath($dom))->query('//p')
				),
				new Access\FakeSubscriber()
			))->visitedAt()
		);
	}
}

(new HtmlPart())->run();
