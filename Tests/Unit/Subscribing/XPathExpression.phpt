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
		$page = new Subscribing\FakePage($dom);
		$expression = new Subscribing\XPathExpression($page, '//p');
		$match = $expression->match();
		Assert::same(1, $match->length);
		Assert::same($match->item(0)->nodeValue, 'Hi there');
		Assert::same($match->item(0)->nodeName, 'p');
	}

	public function testEmptyMatchWithoutError() {
		$dom = new \DOMDocument();
		$dom->loadHTML('<p>Hi there</p>');
		$page = new Subscribing\FakePage($dom);
		$expression = (new Subscribing\XPathExpression($page, '//foo'))->match();
		Assert::same(0, $expression->length);
	}
}

(new XPathExpression())->run();
