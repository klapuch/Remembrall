<?php
declare(strict_types = 1);
namespace Remembrall\UI\Components;

use Spatie\Snapshots;

final class DirectionTest extends \PHPUnit\Framework\TestCase {
	use Snapshots\MatchesSnapshots;

	public function testSortParameterWithNegativeUrlWithTopSymbol() {
		$this->assertMatchesXmlSnapshot((string) new Direction(['sort' => 'url', 'current' => 'url']));
	}

	public function testSortParameterWithPositiveUrlWithBottomSymbol() {
		$this->assertMatchesXmlSnapshot((string) new Direction(['sort' => 'url', 'current' => '-url']));
	}

	public function testNoSortLeadingToNoLinkSpan() {
		$this->assertMatchesXmlSnapshot((string) new Direction(['sort' => null, 'current' => 'url']));
	}

	public function testLinkWithoutSpanForNoCurrent() {
		$this->assertMatchesXmlSnapshot((string) new Direction(['sort' => 'url', 'current' => null]));
	}

	public function testNothingToSortWithNoLinkSpan() {
		$this->assertMatchesXmlSnapshot((string) new Direction(['sort' => null, 'current' => null]));
	}

	public function testNotMatchingCurrentWithoutSpan() {
		$this->assertMatchesXmlSnapshot((string) new Direction(['sort' => 'url', 'current' => 'foo']));
		$this->assertMatchesXmlSnapshot((string) new Direction(['sort' => 'foo', 'current' => 'url']));
	}
}
// @codingStandardsIgnoreStart
final class Direction {
	private $input;

	public function __construct(array $input) {
		$this->input = $input;
	}

	public function __toString(): string {
		$xsl = new \DOMDocument();
		$xsl->load(__DIR__ . '/direction.xsl');
		$xslt = new \XSLTProcessor();
		$xslt->setParameter('', $this->input);
		$xslt->importStylesheet($xsl);
		$xml = new \DOMDocument();
		$xml->loadXML('<field>FIELD</field>');
		$output = new \DOMDocument();
		$output->loadXML($xslt->transformToXml($xml));
		return $output->saveXML();
	}
}
// @codingStandardsIgnoreEnd