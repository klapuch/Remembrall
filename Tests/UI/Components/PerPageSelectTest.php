<?php
declare(strict_types = 1);
namespace Remembrall\UI\Components;

use Spatie\Snapshots;

final class PerPageSelectTest extends \PHPUnit\Framework\TestCase {
	use Snapshots\MatchesSnapshots;

	public function testLabelAsElementContent() {
		$this->assertMatchesXmlSnapshot(
			(string) new PerPageSelect(
				'<select>
					<option label="1">1</option>
					<option label="2">2</option>
				</select>'
			)
		);
	}

	public function testAllowingStringLabelAsElementContent() {
		$this->assertMatchesXmlSnapshot(
			(string) new PerPageSelect(
				'<select>
					<option label="Prompt">1</option>
					<option label="2">2</option>
				</select>'
			)
		);
	}

	public function testSelectedAttributeForCurrentSelection() {
		$this->assertMatchesXmlSnapshot(
			(string) new PerPageSelect(
				'<select>
					<option label="Prompt">20</option>
					<option label="2">30</option>
				</select>',
				20
			)
		);
	}
}
// @codingStandardsIgnoreStart
final class PerPageSelect {
	private $input;
	private $perPage;

	public function __construct(string $input, int $perPage = 0) {
		$this->input = $input;
		$this->perPage = $perPage;
	}

	public function __toString(): string {
		$xsl = new \DOMDocument();
		$xsl->load(__DIR__ . '/per_page_select.xsl');
		$xslt = new \XSLTProcessor();
		$xslt->setParameter('', ['per_page' => $this->perPage]);
		$xslt->registerPHPFunctions();
		$xslt->importStylesheet($xsl);
		$xml = new \DOMDocument();
		$xml->loadXML($this->input);
		$output = new \DOMDocument();
		$output->loadXML($xslt->transformToXml($xml));
		return $output->saveXML();
	}
}
// @codingStandardsIgnoreEnd