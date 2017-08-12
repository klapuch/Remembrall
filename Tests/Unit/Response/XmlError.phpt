<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 * @httpCode any
 */
namespace Remembrall\Unit\Response;

use Remembrall\Response;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class XmlError extends Tester\TestCase {
	public function testForcingXmlHeader() {
		Assert::same(
			['content-type' => 'text/xml; charset=utf8'],
			(new Response\XmlError(new \Exception(), ['Content-Type' => 'xx']))->headers()
		);
	}

	public function testForcingXmlHeaderWithoutCaseSensitivity() {
		Assert::same(
			['content-type' => 'text/xml; charset=utf8'],
			(new Response\XmlError(new \Exception(), ['content-type' => 'xx']))->headers()
		);
	}

	public function testOtherHeadersWithoutRestriction() {
		Assert::same(
			['content-type' => 'text/xml; charset=utf8', 'foo' => 'bar'],
			(new Response\XmlError(new \Exception(), ['foo' => 'bar']))->headers()
		);
	}

	public function testTakingStatusCodeFromException() {
		(new Response\XmlError(new \Exception('', 400)))->headers();
		Assert::same(400, http_response_code());
	}

	public function testStatusCodeFromParameterOnUnknownOneFromException() {
		(new Response\XmlError(new \Exception(), [], 403))->headers();
		Assert::same(403, http_response_code());
	}

	public function testDefaultStatusCodeAsBadRequest() {
		(new Response\XmlError(new \Exception()))->headers();
		Assert::same(400, http_response_code());
	}

	public function testLowerStatusCodeForClientOrServerErrorOnly() {
		(new Response\XmlError(new \Exception('', 200)))->headers();
		Assert::same(400, http_response_code());
		(new Response\XmlError(new \Exception(), [], 200))->headers();
		Assert::same(400, http_response_code());
	}

	public function testHigherStatusCodeForClientOrServerErrorOnly() {
		(new Response\XmlError(new \Exception('', 600)))->headers();
		Assert::same(400, http_response_code());
		(new Response\XmlError(new \Exception(), [], 600))->headers();
		Assert::same(400, http_response_code());
	}

	public function testProperXmlOutput() {
		Assert::same(
			'<?xml version="1.0" encoding="utf-8"?>
<message text="Some error"/>
',
			(new Response\XmlError(new \Exception('Some error')))->body()->serialization()
		);
	}

	public function testNoContentLeadingToDefaultMessage() {
		Assert::same(
			'<?xml version="1.0" encoding="utf-8"?>
<message text="Unknown error, contact support."/>
',
			(new Response\XmlError(new \Exception()))->body()->serialization()
		);
	}

	public function testXssProofContent() {
		Assert::same(
			'<?xml version="1.0" encoding="utf-8"?>
<message text="&lt;&amp;&gt;&quot;\'"/>
',
			(new Response\XmlError(new \Exception('<&>"\'')))->body()->serialization()
		);
	}
}

(new XmlError())->run();