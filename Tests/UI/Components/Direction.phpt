<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\UI\Components;

use Klapuch\Snappie;

require __DIR__ . '/../../bootstrap.php';

final class Direction extends \Tester\TestCase {
	use Snappie\Assertions;

	public function testSortParameterWithNegativeUrlWithTopSymbol() {
		$this->assertXml((string) new DirectionCase(['sort' => 'url', 'current' => 'url']));
	}

	public function testSortParameterWithPositiveUrlWithBottomSymbol() {
		$this->assertXml((string) new DirectionCase(['sort' => 'url', 'current' => '-url']));
	}

	public function testNoSortLeadingToNoLinkSpan() {
		$this->assertXml((string) new DirectionCase(['sort' => null, 'current' => 'url']));
	}

	public function testLinkWithoutSpanForNoCurrent() {
		$this->assertXml((string) new DirectionCase(['sort' => 'url', 'current' => null]));
	}

	public function testNothingToSortWithNoLinkSpan() {
		$this->assertXml((string) new DirectionCase(['sort' => null, 'current' => null]));
	}

	public function testNotMatchingCurrentWithoutSpan() {
		$this->assertXml((string) new DirectionCase(['sort' => 'url', 'current' => 'foo']));
		$this->assertXml((string) new DirectionCase(['sort' => 'foo', 'current' => 'url']));
	}
}
// @codingStandardsIgnoreStart
final class DirectionCase {
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

(new Direction())->run();