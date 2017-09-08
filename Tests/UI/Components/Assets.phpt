<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\UI\Components;

use Klapuch\Snappie;

require __DIR__ . '/../../bootstrap.php';

final class Assets extends \Tester\TestCase {
	use Snappie\Assertions;

	public function testWorkingWithStylesAsLinks() {
		$this->assertXml((string) new AssetsCase('<style href="http://foo.cz"/>'));
	}

	public function testWorkingWithScripts() {
		$this->assertXml((string) new AssetsCase('<script href="http://foo.cz"/>'));
	}

	public function testRemovingTrailingSpacesFromStyles() {
		$this->assertXml((string) new AssetsCase('<style href=" http://foo.cz "/>'));
	}

	public function testRemovingTrailingSpacesFromScripts() {
		$this->assertXml((string) new AssetsCase('<script href=" http://foo.cz "/>'));
	}

	public function testPassingWithUnknownAttributeToStyle() {
		$this->assertXml((string) new AssetsCase('<style foo="http://foo.cz"/>'));
	}

	public function testPassingWithUnknownAttributeToScript() {
		$this->assertXml((string) new AssetsCase('<script foo="http://foo.cz"/>'));
	}

	public function testRelativeScriptHrefTransformedToAbsolute() {
		$this->assertXml((string) new AssetsCase('<script href="foo.js"/>'));
	}

	public function testRelativeStyleHrefTransformedToAbsolute() {
		$this->assertXml((string) new AssetsCase('<script href="bar.css"/>'));
	}
}
// @codingStandardsIgnoreStart
final class AssetsCase {
	private $input;

	public function __construct(string $input) {
		$this->input = $input;
	}

	public function __toString(): string {
		$xsl = new \DOMDocument();
		$xsl->load(__DIR__ . '/../../../App/Page/components/assets.xsl');
		$xslt = new \XSLTProcessor();
		$xslt->importStylesheet($xsl);
		$xslt->setParameter('', 'base_url', '/var/www');
		$xslt->setParameter('', 'nonce', 'random123');
		$xml = new \DOMDocument();
		$xml->loadXML($this->input);
		$output = new \DOMDocument();
		$output->loadXML($xslt->transformToXml($xml));
		return $output->saveXML();
	}
}
// @codingStandardsIgnoreEnd

(new Assets())->run();