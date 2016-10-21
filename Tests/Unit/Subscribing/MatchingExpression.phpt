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

final class MatchingExpression extends Tester\TestCase {
	/**
	 * @throws \Remembrall\Exception\NotFoundException For the given expression there are no matches
	 */
	public function testNoMatch() {
		$dom = new \DOMDocument();
		$dom->loadHTML('<p>Hi there!</p>');
		(new Subscribing\MatchingExpression(
			new Subscribing\FakeExpression('//invalid', new \DOMNodeList())
		))->matches();
	}

	public function testMatchedNodes() {
		Assert::noError(
			function() {
				$dom = new \DOMDocument();
				$dom->loadHTML('<p>Hi there!</p>');
				$nodeList = (new \DOMXPath($dom))->query('//p');
				(new Subscribing\MatchingExpression(
					new Subscribing\FakeExpression('//p', $nodeList)
				))->matches();
			}
		);
	}
}

(new MatchingExpression())->run();
