<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Web;

use Gajus\Dindent;
use Remembrall\Model\Web;
use Tester;
use Tester\Assert;
use Texy;

require __DIR__ . '/../../bootstrap.php';

final class PrettyCode extends Tester\TestCase {
	public function testMakingHtmlCodeSecureAndPretty() {
		Assert::same(
			'<pre class="html"><code> &lt;h1&gt;
    FOO
&lt;/h1&gt;</code></pre>
',
			(string) new Web\PrettyCode(
				'<h1>FOO</h1>',
				new Texy\Texy(),
				new Dindent\Indenter()
			)
		);
	}
}

(new PrettyCode())->run();