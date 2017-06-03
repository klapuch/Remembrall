<?php
declare(strict_types = 1);
namespace Remembrall\UI\Components;

use Spatie\Snapshots;

final class PagerTest extends \PHPUnit\Framework\TestCase {
	use Snapshots\MatchesSnapshots;

	public function testNoPagerForSameFirstAndLast() {
		$this->assertMatchesXmlSnapshot(
			(string) new Pager(
				'<pagination>
					<first>1</first>
					<last>1</last>
				</pagination>'
			)
		);
	}

	public function testNoPagerForSameFirstAndCurrent() {
		$this->assertMatchesXmlSnapshot(
			(string) new Pager(
				'<pagination>
					<first>1</first>
					<current>1</current>
				</pagination>'
			)
		);
	}

	public function testNoPagerForSameLastAndCurrent() {
		$this->assertMatchesXmlSnapshot(
			(string) new Pager(
				'<pagination>
					<last>5</last>
					<current>5</current>
				</pagination>'
			)
		);
	}

	public function testLinkToPrevious() {
		$this->assertMatchesXmlSnapshot(
			(string) new Pager(
				'<pagination>
					<first>1</first>
					<previous>2</previous>
					<current>3</current>
					<last>4</last>
				</pagination>'
			)
		);
	}

	public function testLinkToNext() {
		$this->assertMatchesXmlSnapshot(
			(string) new Pager(
				'<pagination>
					<first>1</first>
					<last>3</last>
					<next>2</next>
					<current>1</current>
				</pagination>'
			)
		);
	}

	public function testNoNextBecauseOfLastPosition() {
		$this->assertMatchesXmlSnapshot(
			(string) new Pager(
				'<pagination>
					<first>1</first>
					<last>3</last>
					<next>3</next>
					<current>2</current>
				</pagination>'
			)
		);
	}
}
// @codingStandardsIgnoreStart
final class Pager {
	private $input;

	public function __construct(string $input) {
		$this->input = $input;
	}

	public function __toString(): string {
		$xsl = new \DOMDocument();
		$xsl->load(__DIR__ . '/../../../App/Page/components/pager.xsl');
		$xslt = new \XSLTProcessor();
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