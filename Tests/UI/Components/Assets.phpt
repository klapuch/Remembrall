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

final class Assets extends Tester\TestCase {
	/**
	 * @dataProvider matches
	 */
	public function testMatches(string $input, string $xpath) {
		$xsl = new \DOMDocument();
		$xsl->load(__DIR__ . '/../../../App/Page/components/assets.xsl');
		$xslt = new \XSLTProcessor();
		$xslt->importStylesheet($xsl);
		$xml = new \DOMDocument();
		$xml->loadXML($input);
		$output = new \DOMDocument();
		$output->loadXML($xslt->transformToXml($xml));
		Assert::true((new \DOMXPath($output))->evaluate($xpath));
	}

	protected function matches(): array {
		return [
			['<style href="foo.cz"/>', 'count(//link[@rel="stylesheet"])=1'],
			['<style href="foo.cz"/>', 'count(//link[@href="foo.cz"])=1'],
			['<style href=" foo.cz "/>', 'count(//link[@href="foo.cz"])=1'],
			['<style foo="bar"/>', 'count(//link[@foo="bar"])=1'],
			['<script href="foo.cz"/>', 'count(//script[@href="foo.cz"])=1'],
			['<script href=" foo.cz "/>', 'count(//script[@href="foo.cz"])=1'],
			['<script foo="bar"/>', 'count(//script[@foo="bar"])=1'],
		];
	}
}

(new Assets())->run();