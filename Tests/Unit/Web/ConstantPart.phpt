<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Web;

use Klapuch\Output;
use Remembrall\Model\Web;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ConstantPart extends Tester\TestCase {
	public function testPrintingFromPassedPart() {
		Assert::same(
			'|snapshot|xxx||id|1||url|google.com|',
			(new Web\ConstantPart(
				new Web\FakePart(),
				'foo',
				'bar',
				['id' => 1, 'url' => 'google.com']
			))->print(new Output\FakeFormat('|snapshot|xxx|'))->serialization()
		);
	}
}

(new ConstantPart())->run();