<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Response;

use Klapuch\Output;
use Remembrall\Response;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ComposedResponse extends Tester\TestCase {
	public function testPuttingTemplateToLayout() {
		$template = Tester\FileMock::create(
			'<?xml version="1.0" encoding="utf-8"?>
			<page>
				<head>
					<title>Error</title>
				</head>
			</page>',
			'xml'
		);
		$layout = Tester\FileMock::create(
			'<?xml version="1.0" encoding="utf-8"?>
			<page>
				<body>
					<header level="1">Error</header>
				</body>
			</page>',
			'xml'
		);
		Assert::same(
			'<?xml version="1.0" encoding="utf-8"?> <page> <head> <title>Error</title> </head> <body> <header level="1">Error</header> </body></page>',
			preg_replace(
				'~\s+~',
				' ',
				(new Response\ComposedResponse(
					new Response\PlainResponse(new Output\FakeFormat()),
					$template,
					$layout
				))->body()->serialization()
			)
		);
	}

	public function testMergingHeaders() {
		Assert::same(
			['Content-Type' => 'text/xml; charset=utf-8;', 'a' => 'b'],
			(new Response\ComposedResponse(
				new Response\PlainResponse(new Output\FakeFormat(), ['a' => 'b']),
				Tester\FileMock::create('', 'xml'),
				Tester\FileMock::create('', 'xml')
			))->headers()
		);
	}

	public function testMergingHeadersWithFormerPrecedence() {
		Assert::same(
			['Content-Type' => 'text/xml; charset=utf-8;'],
			(new Response\ComposedResponse(
				new Response\PlainResponse(new Output\FakeFormat(), ['Content-Type' => 'b']),
				Tester\FileMock::create('', 'xml'),
				Tester\FileMock::create('', 'xml')
			))->headers()
		);
	}
}

(new ComposedResponse())->run();