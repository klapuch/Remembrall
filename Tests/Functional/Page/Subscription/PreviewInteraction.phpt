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

final class PreviewInteraction extends \Tester\TestCase {
	use TestCase\Page;

	public function testAddingAfterPreview() {
		$_SESSION['part'] = ['url' => 'http://www.example.com', 'expression' => '//h1', 'language' => 'xpath'];
		$_POST['interval'] = '44';
		$_POST['act'] = 'Send';
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl(new Uri\FakeUri('', ''), 'subscriptions')
				)
			),
			(new Subscription\PreviewInteraction(
				new Uri\FakeUri('', ''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response($_POST)
		);
	}

	public function testErrorOnAdding() {
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri('', '/subscription/5'), '/subscription/5')
					),
					['danger' => 'Field "interval" is missing in sent data'],
					$_SESSION
				)
			),
			(new Subscription\PreviewInteraction(
				new Uri\FakeUri('', '/subscription/5'),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response($_POST)
		);
	}
}

(new PreviewInteraction())->run();