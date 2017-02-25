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

final class Pager extends Tester\TestCase {
	/**
	 * @dataProvider matches
	 */
	public function testMatches(string $input, string $xpath) {
		$xsl = new \DOMDocument();
		$xsl->load(__DIR__ . '/../../../App/Page/components/pager.xsl');
		$xslt = new \XSLTProcessor();
		$xslt->registerPHPFunctions();
		$xslt->importStylesheet($xsl);
		$xml = new \DOMDocument();
		$xml->loadXML($input);
		$output = new \DOMDocument();
		$output->loadXML((string)$xslt->transformToXml($xml));
		Assert::true((new \DOMXPath($output))->evaluate($xpath));
	}

	protected function matches(): array {
		return [
			[
				'<pagination>
					<first>1</first>
					<last>1</last>
				</pagination>',
				'count(//ul[@class="pager"])=0',
			],
			[
				'<pagination>
					<first>1</first>
					<current>1</current>
				</pagination>',
				'count(//li[@class="previous"])=0',
			],
			[
				'<pagination>
					<last>5</last>
					<current>5</current>
				</pagination>',
				'count(//li[@class="next"])=0',
			],
			[
				'<pagination>
					<first>1</first>
					<previous>2</previous>
					<current>3</current>
					<last>4</last>
				</pagination>',
				'count(//li[@class="previous"]/a[@href="?page=2"])=1',
			],
			[
				'<pagination>
					<first>1</first>
					<last>3</last>
					<next>2</next>
					<current>1</current>
				</pagination>',
				'count(//li[@class="next"]/a[@href="?page=2"])=1',
			],
		];
	}
}

(new Pager())->run();