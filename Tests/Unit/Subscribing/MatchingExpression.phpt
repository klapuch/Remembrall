<?php
declare(strict_types = 1);
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
	public function testThrowingOnNoMatch() {
		$dom = new \DOMDocument();
		$dom->loadHTML('<p>Hi there!</p>');
		(new Subscribing\MatchingExpression(
			new Subscribing\FakeExpression('//invalid', new \DOMNodeList())
		))->matches();
	}

	public function testMatches() {
		$dom = new \DOMDocument();
		$dom->loadHTML('<p>Hi there!</p>');
		Assert::noError(function() use($dom) {
			$expression = '//p';
			$nodeList = (new \DOMXPath($dom))->query($expression);
			(new Subscribing\MatchingExpression(
				new Subscribing\FakeExpression($expression, $nodeList)
			))->matches();
		});
	}
}

(new MatchingExpression())->run();