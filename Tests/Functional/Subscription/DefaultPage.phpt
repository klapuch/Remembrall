<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Subscription;

use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Page\Subscription;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class DefaultPage extends \Tester\TestCase {
	use TestCase\Page;

	public function testWorkingResponse() {
		Assert::noError(function() {
			$body = (new Subscription\DefaultPage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response([])->body()->serialization();
			$dom = new \DOMDocument();
			$dom->loadXML($body);
		});
	}

	public function testAddingSubscription() {
		$_POST['url'] = 'http://www.example.com/';
		$_POST['expression'] = '//h1';
		$_POST['interval'] = '34';
		$_POST['language'] = 'xpath';
		$_POST['act'] = 'Send';
		$headers = (new Subscription\DefaultPage(
			new Uri\FakeUri('', ''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->submitDefault($_POST)->headers();
		Assert::equal($_SESSION['part'], ['url' => 'http://www.example.com'] + $_POST);
		Assert::same(['Location' => '/subscription/preview'], $headers);
	}

	public function testErrorOnAdding() {
		$headers = (new Subscription\DefaultPage(
			new Uri\FakeUri('', '/subscription/5'),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->submitDefault($_POST)->headers();
		Assert::same(['Location' => '/subscription/5'], $headers);
	}
}

(new DefaultPage())->run();