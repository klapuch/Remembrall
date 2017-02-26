<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\UI\Components;

use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class PerPageSelect extends Tester\TestCase {
	/**
	 * @dataProvider matches
	 */
	public function testMatches(string $input, string $xpath) {
		$xsl = new \DOMDocument();
		$xsl->load(__DIR__ . '/per_page_select.xsl');
		$xslt = new \XSLTProcessor();
		$xslt->importStylesheet($xsl);
		$xslt->registerPHPFunctions();
		$xml = new \DOMDocument();
		$xml->loadXML($input);
		$output = new \DOMDocument();
		$output->loadXML($xslt->transformToXml($xml));
		var_dump($output->saveXML());
		Assert::true((new \DOMXPath($output))->evaluate($xpath));
	}

	protected function matches(): array {
		return [
			[
				'<select>
					<option>1</option>
					<option>2</option>
				</select>',
				'count(//select/option)=2'
			],
			[
				'<select>
					<option label="Prompt">1</option>
					<option label="2">2</option>
				</select>',
				'//option[1]/text()="Prompt" and //option[2]/text()="2"'
			],
			[
				'<select>
					<option label="Foo">20</option>
				</select>',
				'contains(//option[1]/@value, "?page=1&per_page=20")'
			],
			[
				'<select>
					<option label="Foo">20</option>
				</select>',
				'count(//option/@selected)=0'
			],
		];
	}
}

(new PerPageSelect())->run();