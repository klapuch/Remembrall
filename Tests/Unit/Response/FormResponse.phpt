<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Response;

use Klapuch\Form;
use Remembrall\Response;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class FormResponse extends Tester\TestCase {
	public function testWrappedForms() {
		Assert::contains(
			'<forms><a>a</a><b>b</b></forms>',
			(new Response\FormResponse(
				new Form\FakeControl('<a>a</a>'),
				new Form\FakeControl('<b>b</b>')
			))->body()->serialization()
		);
	}

	public function testXmlDeclaration() {
		Assert::contains(
			'<?xml',
			(new Response\FormResponse(
				new Form\FakeControl('')
			))->body()->serialization()
		);
	}
}

(new FormResponse())->run();