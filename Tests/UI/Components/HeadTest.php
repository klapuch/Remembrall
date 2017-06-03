<?php
declare(strict_types = 1);
namespace Remembrall\UI\Components;

use Spatie\Snapshots;

final class HeadTest extends \PHPUnit\Framework\TestCase {
	use Snapshots\MatchesSnapshots;

	public function testPassingTitleAsItIs() {
		$this->assertMatchesXmlSnapshot((string) new Head('<title>FOO</title>'));
	}

	public function testRemovingTrailingSpaces() {
		$this->assertMatchesXmlSnapshot((string) new Head('<title> FOO </title>'));
	}

	public function testMetaWithAttributes() {
		$this->assertMatchesXmlSnapshot((string) new Head('<meta name="keywords" content="foo"/>'));
	}

	public function testPassingWithMetaUnknownAttributes() {
		$this->assertMatchesXmlSnapshot((string) new Head('<meta foo="keywords" bar="foo"/>'));
	}

	public function testRemovingSpacesFromAttributes() {
		$this->assertMatchesXmlSnapshot((string) new Head('<meta name=" keywords " content=" foo "/>'));
	}
}
// @codingStandardsIgnoreStart
final class Head {
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