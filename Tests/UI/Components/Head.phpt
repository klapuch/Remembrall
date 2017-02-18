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

final class Head extends Tester\TestCase {
	/**
	 * @dataProvider matches
	 */
	public function testMatches(string $input, string $xpath) {
		$xsl = new \DOMDocument();
		$xsl->load(__DIR__ . '/../../../App/Page/components/head.xsl');
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
			['<title>FOO</title>', 'count(//title[text()="FOO"])=1'],
			['<title> FOO </title>', 'count(//title[text()="FOO"])=1'],
			['<meta foo="bar"/>', 'count(//meta)=1'],
			['<meta name="keywords" content="foo"/>', 'count(//meta[@content="foo" and @name="keywords"])=1'],
			['<meta name=" keywords "/>', 'count(//meta[@name="keywords"])=1'],
		];
	}
}

(new Head())->run();