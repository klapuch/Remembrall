<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Subscribing;

use Klapuch\Output;
use Remembrall\Model\Subscribing;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ConstantParticipant extends Tester\TestCase {
	public function testPrintingFromPassedParticipant() {
		Assert::same(
			'|invited_at|2000-01-01||email|foo@bar.cz||id|12|',
			(new Subscribing\ConstantParticipant(
				['email' => 'foo@bar.cz', 'id' => '12']
			))->print(new Output\FakeFormat('|invited_at|2000-01-01|'))->serialization()
		);
	}
}

(new ConstantParticipant())->run();