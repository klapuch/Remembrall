<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Web;

use Remembrall\Model\Web;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class SuitableExpression extends Tester\TestCase {
	public function testUsingXPathForMatch() {
		$dom = new \DOMDocument();
		$dom->loadHTML('<p class="text">Hi there</p>');
		$expression = new Web\SuitableExpression(
			'xpath',
			new Web\FakePage($dom),
			'//p[@class="text"]'
		);
		$match = $expression->matches();
		Assert::same(1, $match->length);
		Assert::same($match->item(0)->nodeValue, 'Hi there');
		Assert::same($match->item(0)->nodeName, 'p');
	}

	public function testUsingCssForMatch() {
		$dom = new \DOMDocument();
		$dom->loadHTML('<p class="text">Hi there</p>');
		$expression = new Web\SuitableExpression(
			'css',
			new Web\FakePage($dom),
			'p.text'
		);
		$match = $expression->matches();
		Assert::same(1, $match->length);
		Assert::same($match->item(0)->nodeValue, 'Hi there');
		Assert::same($match->item(0)->nodeName, 'p');
	}

	/**
	 * @throws \UnexpectedValueException Allowed languages are "xpath", "css" - "foo" given
	 */
	public function testThrowingOnUnknownChoice() {
		(new Web\SuitableExpression(
			'foo',
			new Web\FakePage(),
			'//p[@class="text"]'
		))->matches();
	}
}

(new SuitableExpression())->run();