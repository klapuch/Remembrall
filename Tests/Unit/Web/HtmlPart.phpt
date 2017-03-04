<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Web;

use Klapuch\Output;
use Remembrall\Model\Web;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class HtmlPart extends Tester\TestCase {
	public function testShrinkingHtml() {
		$dom = new \DOMDocument();
		$dom->loadHTML(
			'
			<div>
				<p>Hi<span>John</span>How are <span>you</span></p>
				<p><span>I am</span>thank<span>you</span></p>
				<p class="blank">Blank</p>
				<p>Invalid<p>
			</div>
			'
		);
		$expression = '//p';
		Assert::same(
			'<p>Hi<span>John</span>How are <span>you</span></p><p><span>I am</span>thank<span>you</span></p><p class="blank">Blank</p><p>Invalid</p><p></p>',
			(new Web\HtmlPart(
				new Web\FakeExpression(
					$expression,
					(new \DOMXPath($dom))->query($expression)
				),
				new Web\FakePage()
			))->content()
		);
	}

	public function testAllowingEmptyPart() {
		$expression = '//invalid';
		$dom = new \DOMDocument();
		$dom->loadHTML('<div><p>Blank</p></div>');
		Assert::same(
			'',
			(new Web\HtmlPart(
				new Web\FakeExpression(
					$expression,
					(new \DOMXPath($dom))->query($expression)
				),
				new Web\FakePage()
			))->content()
		);
	}

	public function testSnapshotHash() {
		$expression = '//p';
		$dom = new \DOMDocument();
		$dom->loadHTML('<div><p>SNAPSHOT</p></div>');
		Assert::same(
			sha1('<p>SNAPSHOT</p>'),
			(new Web\HtmlPart(
				new Web\FakeExpression(
					$expression,
					(new \DOMXPath($dom))->query($expression)
				),
				new Web\FakePage()
			))->snapshot()
		);
	}

	public function testSnapshotLength() {
		$expression = '//p';
		$dom = new \DOMDocument();
		$dom->loadHTML('<div><p>Blank</p></div>');
		Assert::same(
			40,
			strlen(
				(new Web\HtmlPart(
					new Web\FakeExpression(
						$expression,
						(new \DOMXPath($dom))->query($expression)
					),
					new Web\FakePage()
				))->snapshot()
			)
		);
	}

	public function testRefreshingWithPage() {
		$expression = '//p';
		$dom = new \DOMDocument();
		$dom->loadHTML('<p>XXX</p>');
		$refreshedPart = new Web\FakePage($dom);
		Assert::equal(
			new Web\HtmlPart(
				new Web\FakeExpression($expression),
				$refreshedPart
			),
			(new Web\HtmlPart(
				new Web\FakeExpression($expression),
				new Web\FakePage(null, $refreshedPart)
			))->refresh()
		);
	}

	public function testPrintingPassedFormat() {
		Assert::same(
			'foo',
			(new Web\HtmlPart(
				new Web\FakeExpression(),
				new Web\FakePage()
			))->print(new Output\FakeFormat('foo'))->serialization()
		);
	}
}

(new HtmlPart())->run();