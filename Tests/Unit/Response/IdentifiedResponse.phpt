<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Response;

use Klapuch\Access;
use Remembrall\Response;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class IdentifiedResponse extends Tester\TestCase {
	public function testPrintingUserWithProperties() {
		Assert::contains(
			'<user id="1" role="member" email="foo@bar.cz"/>',
			(new Response\IdentifiedResponse(
				new Access\FakeUser(1, ['role' => 'member', 'email' => 'foo@bar.cz'])
			))->body()->serialization()
		);
	}

	public function testPrintingWithoutProperties() {
		Assert::contains(
			'<user id="1"/>',
			(new Response\IdentifiedResponse(
				new Access\FakeUser(1, [])
			))->body()->serialization()
		);
	}

	public function testPrintingWithXmlDeclaration() {
		Assert::contains(
			'<?xml',
			(new Response\IdentifiedResponse(
				new Access\FakeUser(1, [])
			))->body()->serialization()
		);
	}
}

(new IdentifiedResponse())->run();