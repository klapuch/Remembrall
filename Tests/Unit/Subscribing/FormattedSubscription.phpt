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
use Texy;

require __DIR__ . '/../../bootstrap.php';

final class FormattedSubscription extends Tester\TestCase {
	public function testFormattingHtmlContent() {
		Assert::same(
			'<content>&lt;pre class=&quot;html&quot;&gt;&lt;code&gt;&amp;lt;h1&amp;gt;FOO&amp;lt;/h1&amp;gt;&lt;/code&gt;&lt;/pre&gt;
</content>',
			(new Subscribing\FormattedSubscription(
				new Subscribing\FakeSubscription(),
				new Texy\Texy()
			))->print(new Output\Xml(['content' => '<h1>FOO</h1>']))->serialization()
		);
	}

	public function testFormattingToHumanReadableDateTime() {
		Assert::same(
			'<last_update>2017-07-04 12:22</last_update>',
			(new Subscribing\FormattedSubscription(
				new Subscribing\FakeSubscription(),
				new Texy\Texy()
			))->print(new Output\Xml(['last_update' => '2017-07-04 12:22:40.533306']))->serialization()
		);
	}
}

(new FormattedSubscription())->run();