<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\UI;

use Tester;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

final class Direction extends Tester\TestCase {
	/**
	 * @dataProvider matches
	 */
	public function testMatches(array $input, string $xpath) {
		$xsl = new \DOMDocument();
		$xsl->load('direction.xsl');
		$xslt = new \XSLTProcessor();
		$xslt->setParameter('', $input);
		$xslt->importStylesheet($xsl);
		$xml = new \DOMDocument();
		$xml->loadXML('<field>FIELD</field>');
		$output = new \DOMDocument();
		$output->loadXML($xslt->transformToXml($xml));
		Assert::true((new \DOMXPath($output))->evaluate($xpath));
		Assert::true((new \DOMXPath($output))->evaluate('count(//p[text()="FIELD"])=1'));
	}

	protected function matches(): array {
		return [
			[['sort' => 'url', 'current' => 'url'], 'count(//a[@href="?sort=-url"])=1'],
			[['sort' => 'url', 'current' => 'url'], 'count(//span[@class="glyphicon glyphicon-triangle-top"])=1'],
			[['sort' => 'url', 'current' => '-url'], 'count(//a[@href="?sort=url"])=1'],
			[['sort' => 'url', 'current' => '-url'], 'count(//span[@class="glyphicon glyphicon-triangle-bottom"])=1'],
			[['sort' => null, 'current' => 'url'], 'count(//a)=0'],
			[['sort' => null, 'current' => 'url'], 'count(//span)=0'],
			[['sort' => 'url', 'current' => null], 'count(//a[@href="?sort=url"])=1'],
			[['sort' => 'url', 'current' => null], 'count(//span)=0'],
			[['sort' => null, 'current' => null], 'count(//a)=0'],
			[['sort' => null, 'current' => null], 'count(//span)=0'],
			[['sort' => 'url', 'current' => 'foo'], 'count(//a[@href="?sort=url"])=1'],
			[['sort' => 'url', 'current' => 'foo'], 'count(//span)=0'],
			[['sort' => 'foo', 'current' => 'url'], 'count(//a[@href="?sort=foo"])=1'],
			[['sort' => 'foo', 'current' => 'url'], 'count(//span)=0'],
		];
	}
}

(new Direction())->run();