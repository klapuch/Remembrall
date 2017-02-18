<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Subscribing;

use Klapuch\Dataset;
use Remembrall\Model\Subscribing;
use Tester;
use Tester\Assert;
use Texy;

require __DIR__ . '/../../bootstrap.php';

final class FormattedSubscriptions extends Tester\TestCase {
	public function testApplyingToAllDuringIterating() {
		Assert::equal(
			[
				new Subscribing\FormattedSubscription(
					new Subscribing\FakeSubscription(null),
					new Texy\Texy()
				),
				new Subscribing\FormattedSubscription(
					new Subscribing\FakeSubscription(null),
					new Texy\Texy()
				),
			],
			iterator_to_array(
				(new Subscribing\FormattedSubscriptions(
					new Subscribing\FakeSubscriptions(
						null,
						new Subscribing\FakeSubscription(null),
						new Subscribing\FakeSubscription(null)
					),
					new Texy\Texy()
				))->iterate(new Dataset\FakeSelection())
			)
		);
	}
}

(new FormattedSubscriptions())->run();