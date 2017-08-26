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

final class MatchingExpression extends Tester\TestCase {
	/**
	 * @throws \UnexpectedValueException For the given expression there are no matches
	 */
	public function testThrowingOnNoMatch() {
		$dom = new \DOMDocument();
		$dom->loadHTML('<p>Hi there!</p>');
		(new Web\MatchingExpression(
			new Web\FakeExpression('//invalid', new \DOMNodeList())
		))->matches();
	}

	public function testPassingMatches() {
		$dom = new \DOMDocument();
		$dom->loadHTML('<p>Hi there!</p>');
		Assert::noError(function() use ($dom) {
			$expression = '//p';
			$nodeList = (new \DOMXPath($dom))->query($expression);
			(new Web\MatchingExpression(
				new Web\FakeExpression($expression, $nodeList)
			))->matches();
		});
	}
}

(new MatchingExpression())->run();