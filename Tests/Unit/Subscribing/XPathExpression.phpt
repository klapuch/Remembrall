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

	public function testInvalidExpressionWithout() {
		Assert::same(
			(string)new Subscribing\XPathExpression(
				new Subscribing\FakePage,
				'123'
			),
			'123'
		);
	}

	public function testMatching() {
		$dom = new \DOMDocument();
		$dom->loadHTML('<p>Hi there</p>');
		$page = new Subscribing\FakePage($dom);
		$expression = new Subscribing\XPathExpression($page, '//p');
		$match = $expression->matches();
		Assert::same(1, $match->length);
		Assert::same($match->item(0)->nodeValue, 'Hi there');
		Assert::same($match->item(0)->nodeName, 'p');
	}

	public function testNoMatch() {
		$dom = new \DOMDocument();
		$dom->loadHTML('<p>Hi there</p>');
		$page = new Subscribing\FakePage($dom);
		$expression = (new Subscribing\XPathExpression($page, '//foo'))->matches();
		Assert::same(0, $expression->length);
	}
}

(new XPathExpression())->run();
