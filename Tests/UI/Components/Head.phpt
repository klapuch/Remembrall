<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\UI\Components;

use Klapuch\Snappie;

require __DIR__ . '/../../bootstrap.php';

final class Head extends \Tester\TestCase {
	use Snappie\Assertions;

	public function testPassingTitleAsItIs() {
		$this->assertXml((string) new HeadCase('<title>FOO</title>'));
	}

	public function testRemovingTrailingSpaces() {
		$this->assertXml((string) new HeadCase('<title> FOO </title>'));
	}

	public function testMetaWithAttributes() {
		$this->assertXml((string) new HeadCase('<meta name="keywords" content="foo"/>'));
	}

	public function testPassingWithMetaUnknownAttributes() {
		$this->assertXml((string) new HeadCase('<meta foo="keywords" bar="foo"/>'));
	}

	public function testRemovingSpacesFromAttributes() {
		$this->assertXml((string) new HeadCase('<meta name=" keywords " content=" foo "/>'));
	}
}
// @codingStandardsIgnoreStart
final class HeadCase {
	private $input;

	public function __construct(string $input) {
		$this->input = $input;
	}

	public function __toString(): string {
		$xsl = new \DOMDocument();
		$xsl->load(__DIR__ . '/../../../App/Page/components/head.xsl');
		$xslt = new \XSLTProcessor();
		$xslt->importStylesheet($xsl);
		$xml = new \DOMDocument();
		$xml->loadXML($this->input);
		$output = new \DOMDocument();
		$output->loadXML($xslt->transformToXml($xml));
		return $output->saveXML();
	}
}
// @codingStandardsIgnoreEnd

(new Head())->run();