<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Subscribing;

use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester;

require __DIR__ . '/../../bootstrap.php';

final class ValidXPathExpression extends Tester\TestCase {
	/**
	 * @throws \Remembrall\Exception\ExistenceException XPath expression "//foo" does not exist on the "http://www.google.com" page
	 */
	public function testEmptyMatchWithError() {
		$dom = new \DOMDocument();
		$dom->loadHTML('<p>Hi there!</p>');
		(new Subscribing\ValidXPathExpression(
			new Subscribing\FakeExpression('//foo'),
			new Subscribing\FakePage('http://www.google.com', $dom)
		))->match();
	}
}

(new ValidXPathExpression())->run();
