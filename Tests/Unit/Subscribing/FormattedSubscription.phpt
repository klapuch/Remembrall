<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Subscribing;

use Gajus\Dindent;
use Klapuch\Output;
use Remembrall\Model\Subscribing;
use Tester;
use Tester\Assert;
use Texy;

require __DIR__ . '/../../bootstrap.php';

final class FormattedSubscription extends Tester\TestCase {
	public function testFormattingHtmlContent() {
		Assert::same(
			'<pre class="html"><code> &lt;h1&gt;
    FOO
&lt;/h1&gt;</code></pre>
',
			(new Subscribing\FormattedSubscription(
				new Subscribing\FakeSubscription(),
				new Texy\Texy(),
				new Dindent\Indenter()
			))->print(new Output\ArrayFormat(['content' => '<h1>FOO</h1>']))->serialization()
		);
	}

	public function testFormattingDateTimeToHumanReadableForm() {
		Assert::same(
			'2017-07-04 12:22',
			(new Subscribing\FormattedSubscription(
				new Subscribing\FakeSubscription(),
				new Texy\Texy(),
				new Dindent\Indenter()
			))->print(new Output\ArrayFormat(['last_update' => '2017-07-04 12:22:40.533306']))->serialization()
		);
	}

	public function testFormattingIntervalToHumanReadableForm() {
		Assert::same(
			'33 minutes',
			(new Subscribing\FormattedSubscription(
				new Subscribing\FakeSubscription(),
				new Texy\Texy(),
				new Dindent\Indenter()
			))->print(new Output\ArrayFormat(['interval' => 'PT1980S']))->serialization()
		);
	}

	public function testFormattingLanguageToHumanReadableForm() {
		Assert::same(
			'XPath',
			(new Subscribing\FormattedSubscription(
				new Subscribing\FakeSubscription(),
				new Texy\Texy(),
				new Dindent\Indenter()
			))->print(new Output\ArrayFormat(['language' => 'xpath']))->serialization()
		);
		Assert::same(
			'CSS',
			(new Subscribing\FormattedSubscription(
				new Subscribing\FakeSubscription(),
				new Texy\Texy(),
				new Dindent\Indenter()
			))->print(new Output\ArrayFormat(['language' => 'css']))->serialization()
		);
	}
}

(new FormattedSubscription())->run();