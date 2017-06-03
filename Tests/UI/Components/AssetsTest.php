<?php
declare(strict_types = 1);
namespace Remembrall\UI\Components;

use Spatie\Snapshots;

final class AssetsTest extends \PHPUnit\Framework\TestCase {
	use Snapshots\MatchesSnapshots;

	public function testWorkingWithStylesAsLinks() {
		$this->assertMatchesXmlSnapshot((string) new Assets('<style href="http://foo.cz"/>'));
	}

	public function testWorkingWithScripts() {
		$this->assertMatchesXmlSnapshot((string) new Assets('<script href="http://foo.cz"/>'));
	}

	public function testRemovingTrailingSpacesFromStyles() {
		$this->assertMatchesXmlSnapshot((string) new Assets('<style href=" http://foo.cz "/>'));
	}

	public function testRemovingTrailingSpacesFromScripts() {
		$this->assertMatchesXmlSnapshot((string) new Assets('<script href=" http://foo.cz "/>'));
	}

	public function testPassingWithUnknownAttributeToStyle() {
		$this->assertMatchesXmlSnapshot((string) new Assets('<style foo="http://foo.cz"/>'));
	}

	public function testPassingWithUnknownAttributeToScript() {
		$this->assertMatchesXmlSnapshot((string) new Assets('<script foo="http://foo.cz"/>'));
	}

	public function testRelativeScriptHrefTransformedToAbsolute() {
		$this->assertMatchesXmlSnapshot((string) new Assets('<script href="foo.js"/>'));
	}

	public function testRelativeStyleHrefTransformedToAbsolute() {
		$this->assertMatchesXmlSnapshot((string) new Assets('<script href="bar.css"/>'));
	}
}
// @codingStandardsIgnoreStart
final class Assets {
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