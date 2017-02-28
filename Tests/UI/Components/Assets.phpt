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
		$xslt->setParameter('', 'base_url', '/var/www');
		$xml = new \DOMDocument();
		$xml->loadXML($input);
		$output = new \DOMDocument();
		$output->loadXML($xslt->transformToXml($xml));
		Assert::true((new \DOMXPath($output))->evaluate($xpath));
	}

	protected function matches(): array {
		return [
			['<style href="http://foo.cz"/>', 'count(//link[@rel="stylesheet"])=1'],
			['<style href="http://foo.cz"/>', 'count(//link[@href="http://foo.cz"])=1'],
			['<style href=" http://foo.cz "/>', 'count(//link[@href="http://foo.cz"])=1'],
			['<style foo="http://bar"/>', 'count(//link[@foo="http://bar"])=1'],
			['<script href="http://foo.cz"/>', 'count(//script[@href="http://foo.cz"])=1'],
			['<script href=" http://foo.cz "/>', 'count(//script[@href="http://foo.cz"])=1'],
			['<script foo="http://bar"/>', 'count(//script[@foo="http://bar"])=1'],
			['<style href="foo.css"/>', 'count(//link[@href="/var/www/foo.css"])=1'],
		];
	}
}

(new Assets())->run();