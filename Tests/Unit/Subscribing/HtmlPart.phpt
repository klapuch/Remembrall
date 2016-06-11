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
		$nodes = (new \DOMXPath($dom))->query('//p');
		Assert::same(
			'<p>Hi<span>John</span>How are <span>you</span></p><p><span>I am</span>thank<span>you</span></p><p>Blank</p><p>Invalid</p><p></p>',
			(new Subscribing\HtmlPart(
				new Subscribing\FakePage,
				$nodes
			))->content()
		);
	}

	public function testEmptyNodes() {
		$dom = new \DOMDocument();
		$dom->loadHTML('<div><p>Blank</p></div>');
		$nodes = (new \DOMXPath($dom))->query('//span'); // nodes with 0 length
		Assert::same(
			'',
			(new Subscribing\HtmlPart(
				new Subscribing\FakePage,
				$nodes
			))->content()
		);
	}

	public function testSameContentButDifferentPage() {
		Assert::false(
			(new Subscribing\HtmlPart(
				new Subscribing\FakePage('google.com'),
				new \DOMNodeList
			))->equals(
				new Subscribing\FakePart(
					'',
					new Subscribing\FakePage('seznam.cz')
				)
			)
		);
	}

	public function testDifferentContentButSamePage() {
		Assert::false(
			(new Subscribing\HtmlPart(
				new Subscribing\FakePage('google.com'),
				new \DOMNodeList
			))->equals(
				new Subscribing\FakePart(
					'abc',
					new Subscribing\FakePage('google.com')
				)
			)
		);
	}

	public function testEquivalentParts() {
		Assert::true(
			(new Subscribing\HtmlPart(
				new Subscribing\FakePage('google.com'),
				new \DOMNodeList
			))->equals(
				new Subscribing\FakePart(
					'',
					new Subscribing\FakePage('google.com')
				)
			)
		);
	}
}

(new HtmlPart())->run();
