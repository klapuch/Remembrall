<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Web;

use Gajus\Dindent;
use Klapuch\Output;
use Remembrall\Model\Web;
use Tester;
use Tester\Assert;
use Texy;

require __DIR__ . '/../../bootstrap.php';

final class FormattedPart extends Tester\TestCase {
	public function testFormattingHtmlContent() {
		Assert::same(
			'<pre class="html"><code> &lt;h1&gt;
    FOO
&lt;/h1&gt;</code></pre>
',
			(new Web\FormattedPart(
				new Web\FakePart(),
				new Texy\Texy(),
				new Dindent\Indenter()
			))->print(new Output\ArrayFormat(['content' => '<h1>FOO</h1>']))->serialization()
		);
	}
}

(new FormattedPart())->run();