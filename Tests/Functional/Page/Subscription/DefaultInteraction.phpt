<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Page\Subscription;

use Klapuch\Application;
use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Page\Subscription;
use Remembrall\Response;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class DefaultInteraction extends \Tester\TestCase {
	use TestCase\Page;

	public function testAddingSubscription() {
		$_POST['url'] = 'http://www.example.com/';
		$_POST['expression'] = '//h1';
		$_POST['language'] = 'xpath';
		$_POST['act'] = 'Send';
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl(new Uri\FakeUri('', ''), 'subscription/preview')
				)
			),
			(new Subscription\DefaultInteraction(
				new Uri\FakeUri('', ''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->template($_POST)
		);
		Assert::equal($_SESSION['part'], $_POST);
	}

	public function testErrorOnAdding() {
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri('', '/subscription/5'), '/subscription/5')
					),
					['danger' => 'Field "url" is missing in sent data'],
					$_SESSION
				)
			),
			(new Subscription\DefaultInteraction(
				new Uri\FakeUri('', '/subscription/5'),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->template($_POST)
		);
	}
}

(new DefaultInteraction())->run();