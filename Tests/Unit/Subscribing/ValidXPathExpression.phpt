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

final class ValidXPathExpression extends Tester\TestCase {
	/**
	 * @throws \Remembrall\Exception\NotFoundException XPath expression does not exist
	 */
	public function testEmptyMatchWithError() {
		$dom = new \DOMDocument();
		$dom->loadHTML('<p>Hi there!</p>');
		(new Subscribing\ValidXPathExpression(
			new Subscribing\FakeExpression('//foo', new \DOMNodeList())
		))->matches();
	}

	public function testMatchedSomeNodes() {
		Assert::noError(
			function() {
				$dom = new \DOMDocument();
				$dom->loadHTML('<p>Hi there!</p>');
				$nodeList = (new \DOMXPath($dom))->query('//p');
				(new Subscribing\ValidXPathExpression(
					new Subscribing\FakeExpression('//p', $nodeList)
				))->matches();
			}
		);
	}
}

(new ValidXPathExpression())->run();
