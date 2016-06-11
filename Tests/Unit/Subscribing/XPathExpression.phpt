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

final class XPathExpression extends Tester\TestCase {
	public function testValidExpression() {
		Assert::same(
			(string)new Subscribing\XPathExpression(
				new Subscribing\FakePage,
				'//p'
			),
			'//p'
		);
	}

	public function testInvalidExpressionWithoutError() {
		Assert::same(
			(string)new Subscribing\XPathExpression(
				new Subscribing\FakePage,
				'123'
			),
			'123'
		);
	}

	public function testMatchedPart() {
		$dom = new \DOMDocument();
		$dom->loadHTML('<p>Hi there</p>');
		$page = new Subscribing\FakePage(null, $dom);
		$expression = new Subscribing\XPathExpression($page, '//p');
		$nodes = (new \DOMXPath($dom))->query('//p');
		Assert::equal(
			new Subscribing\HtmlPart($page, $nodes),
			$expression->match()
		);
	}

	public function testEmptyMatchWithoutError() {
		$dom = new \DOMDocument();
		$dom->loadHTML('<p>Hi there</p>');
		$page = new Subscribing\FakePage(null, $dom);
		$expression = new Subscribing\XPathExpression($page, '//foo');
		$nodes = (new \DOMXPath($dom))->query('//foo');
		Assert::same(0, $nodes->length);
		Assert::equal(
			new Subscribing\HtmlPart($page, $nodes),
			$expression->match()
		);
	}
}

(new XPathExpression())->run();
