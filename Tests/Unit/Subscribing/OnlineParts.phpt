<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Subscribing;

use Remembrall\Model\{
	Subscribing, Http
};
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class OnlineParts extends Tester\TestCase {
	public function testIteratingWithOnlineParts() {
		$oldContent = new \DOMDocument();
		$oldContent->loadHTML('<p>Hello</p>');
		$oldPage = new Subscribing\FakePage($oldContent);
		$freshContent = new \DOMDocument();
		$freshContent->loadHTML('<p>Hello There!</p>');
		$onlinePage = new Subscribing\FakePage($oldContent);
		$expression = new Subscribing\FakeExpression('//a');
		$parts = (new Subscribing\OnlineParts(
			new Subscribing\FakeParts(
				[
					new Subscribing\FakePart(
						'a',
						'www.a.cz',
						null,
						$expression,
						$oldPage
					),
				]
			),
			new Http\FakeRequest($onlinePage)
		))->iterate();
		Assert::equal(
			new Subscribing\ConstantPart(
				new Subscribing\HtmlPart(
					new Subscribing\XPathExpression(
						new Subscribing\ConstantPage(
							$onlinePage,
							$oldContent->saveHTML()
						),
						$expression
					),
					new Subscribing\ConstantPage(
						$onlinePage,
						$oldContent->saveHTML()
					)
				),
				'a',
				'www.a.cz'
			),
			$parts[0]
		);
	}
}

(new OnlineParts())->run();
