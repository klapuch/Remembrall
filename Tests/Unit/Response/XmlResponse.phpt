<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 * @httpCode any
 */
namespace Remembrall\Unit\Response;

use Klapuch\Output;
use Remembrall\Response;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class XmlResponse extends Tester\TestCase {
	public function testForcingXmlHeader() {
		Assert::same(
			['content-type' => 'text/xml; charset=utf8'],
			(new Response\XmlResponse(
				new Response\PlainResponse(
					new Output\FakeFormat('foo'),
					['Content-Type' => 'bar']
				)
			))->headers()
		);
	}

	public function testOtherHeadersWithoutRestriction() {
		Assert::same(
			['content-type' => 'text/xml; charset=utf8', 'foo' => 'bar'],
			(new Response\XmlResponse(
				new Response\PlainResponse(
					new Output\FakeFormat('foo'),
					['foo' => 'bar']
				)
			))->headers()
		);
	}

	public function testDefaultStatusCode() {
		(new Response\XmlResponse(
			new Response\PlainResponse(new Output\FakeFormat('foo'))
		))->headers();
		Assert::same(200, http_response_code());
	}

	public function testCustomStatusCode() {
		(new Response\XmlResponse(
			new Response\PlainResponse(new Output\FakeFormat('foo')),
			302
		))->headers();
		Assert::same(302, http_response_code());
	}

	public function testThrowingOnInvalidDocument() {
		$ex = Assert::exception(function() {
			(new Response\XmlResponse(
				new Response\PlainResponse(new Output\FakeFormat('foo'), [])
			))->body();
		}, \UnexpectedValueException::class, 'XML document is not valid');
		Assert::type(\Throwable::class, $ex->getPrevious());
		Assert::same("Start tag expected, '<' not found", $ex->getPrevious()->getMessage());
	}

	public function testEnablingOldStateOfErrors() {
		$switch = libxml_use_internal_errors();
		Assert::exception(function() {
			(new Response\XmlResponse(
				new Response\PlainResponse(new Output\FakeFormat('foo'), [])
			))->body();
		}, \Throwable::class);
		Assert::same($switch, libxml_use_internal_errors());
	}

	public function testRewritingEncodingToUtf8() {
		Assert::equal(
			'<?xml version="1.0" encoding="utf-8"?>
<foo/>
',
			(new Response\XmlResponse(
				new Response\PlainResponse(
					new Output\FakeFormat('<?xml version="1.0"?><foo/>'),
					[]
				)
			))->body()->serialization()
		);
	}

	public function testAddingXmlDeclaration() {
		Assert::equal(
			'<?xml version="1.0" encoding="utf-8"?>
<foo/>
',
			(new Response\XmlResponse(
				new Response\PlainResponse(
					new Output\FakeFormat('<foo/>'),
					[]
				)
			))->body()->serialization()
		);
	}

	public function testMakingEmptyTag() {
		Assert::equal(
			'<?xml version="1.0" encoding="utf-8"?>
<foo id="1"/>
',
			(new Response\XmlResponse(
				new Response\PlainResponse(
					new Output\FakeFormat('<?xml version="1.0"?><foo id="1"></foo>'),
					[]
				)
			))->body()->serialization()
		);
	}
}

(new XmlResponse())->run();