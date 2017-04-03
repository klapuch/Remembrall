<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Web;

use Gajus\Dindent;
use Klapuch\Dataset;
use Remembrall\Model\Web;
use Tester;
use Tester\Assert;
use Texy;

require __DIR__ . '/../../bootstrap.php';

final class FormattedParts extends Tester\TestCase {
	public function testApplyingToAllDuringIterating() {
		Assert::equal(
			[
				new Web\FormattedPart(
					new Web\FakePart('foo'),
					new Texy\Texy(),
					new Dindent\Indenter()
				),
				new Web\FormattedPart(
					new Web\FakePart('bar'),
					new Texy\Texy(),
					new Dindent\Indenter()
				),
			],
			iterator_to_array(
				(new Web\FormattedParts(
					new Web\FakeParts(
						null,
						new Web\FakePart('foo'),
						new Web\FakePart('bar')
					),
					new Texy\Texy(),
					new Dindent\Indenter()
				))->all(new Dataset\FakeSelection())
			)
		);
	}
}

(new FormattedParts())->run();