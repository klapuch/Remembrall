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

final class ConstantSubscription extends Tester\TestCase {
	public function testPrintingFromPassedPart() {
		Assert::same(
			'|prev|xxx||interval|5 minutes||visited_at|2015-01-01||interval|PT5M||expression|//p|',
			(new Subscribing\ConstantSubscription(
				new Subscribing\FakeSubscription(),
				[
					'visited_at' => '2015-01-01',
					'interval' => 'PT5M',
					'expression' => '//p'
				]
			))->print(new Output\FakeFormat('|prev|xxx|'))->serialization()
		);
	}
}

(new ConstantSubscription())->run();