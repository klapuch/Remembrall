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
			(new Response\XmlError('Some error', 404, ['Content-Type' => 'xx']))->headers()
		);
	}

	public function testForcingXmlHeaderWithoutCaseSensitivity() {
		Assert::same(
			['content-type' => 'text/xml; charset=utf8'],
			(new Response\XmlError('Some error', 404, ['content-type' => 'xx']))->headers()
		);
	}

	public function testOtherHeadersWithoutRestriction() {
		Assert::same(
			['content-type' => 'text/xml; charset=utf8', 'foo' => 'bar'],
			(new Response\XmlError('Some error', 404, ['foo' => 'bar']))->headers()
		);
	}

	public function testDefaultStatusCode() {
		(new Response\XmlError('Some error'))->headers();
		Assert::same(400, http_response_code());
	}

	public function testCustomStatusCode() {
		(new Response\XmlError('Some error', 403))->headers();
		Assert::same(403, http_response_code());
	}

	public function testStatusCodeForClientOrServerErrorOnly() {
		(new Response\XmlError('Some error', 200))->headers();
		Assert::same(400, http_response_code());
		(new Response\XmlError('Some error', 600))->headers();
		Assert::same(400, http_response_code());
	}

	public function testProperXmlOutput() {
		Assert::same(
			'<?xml version="1.0" encoding="utf-8"?>
<message text="Some error"/>
',
			(new Response\XmlError('Some error'))->body()->serialization()
		);
	}

	public function testXssProofContent() {
		Assert::same(
			'<?xml version="1.0" encoding="utf-8"?>
<message text="&lt;&amp;&gt;&quot;\'"/>
',
			(new Response\XmlError('<&>"\''))->body()->serialization()
		);
	}
}

(new XmlError())->run();