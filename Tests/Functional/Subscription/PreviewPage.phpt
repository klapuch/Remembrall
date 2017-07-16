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
use Remembrall\Model\Web\FakePart;
use Remembrall\Model\Web\TemporaryParts;
use Remembrall\Page\Subscription;
use Remembrall\Response;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class PreviewPage extends \Tester\TestCase {
	use TestCase\Page;

	public function testWorkingResponse() {
		$_SESSION['part'] = ['url' => 'http://www.example.com', 'expression' => '//h1', 'language' => 'xpath'];
		(new TemporaryParts(
			$this->redis
		))->add(
			new FakePart(''),
			new Uri\FakeUri($_SESSION['part']['url']),
			$_SESSION['part']['expression'],
			$_SESSION['part']['language']
		);
		Assert::noError(function() {
			$body = (new Subscription\PreviewPage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response([])->body()->serialization();
			$dom = new \DOMDocument();
			$dom->loadXML($body);
		});
	}

	public function testErrorOnNotFoundPart() {
		$_SESSION['part'] = ['url' => 'http://www.example.com', 'expression' => '//h1', 'language' => 'xpath'];
		Assert::equal(
			new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl(new Uri\FakeUri(''), 'subscription')
				),
				['danger' => 'Part not found'],
				$_SESSION
			),
			(new Subscription\PreviewPage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response([])
		);
	}

	public function testMissingSessionFieldForResponseLeadingToError() {
		Assert::equal(
			new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl(new Uri\FakeUri(''), 'subscription')
				),
				['danger' => 'Missing referenced part'],
				$_SESSION
			),
			(new Subscription\PreviewPage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response([])
		);
	}

	public function testMissingSomeSessionFieldForResponseLeadingToError() {
		$_SESSION['part'] = ['language' => 'xpath'];
		Assert::equal(
			new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl(new Uri\FakeUri(''), 'subscription')
				),
				['danger' => 'Missing referenced part'],
				$_SESSION
			),
			(new Subscription\PreviewPage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response([])
		);
	}

	public function testAddingAfterPreview() {
		$_SESSION['part'] = ['url' => 'http://www.example.com', 'expression' => '//h1', 'language' => 'xpath'];
		$_POST['interval'] = '44';
		$_POST['act'] = 'Send';
		$headers = (new Subscription\PreviewPage(
			new Uri\FakeUri('', ''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->submitPreview($_POST)->headers();
		Assert::same(['Location' => '/subscriptions'], $headers);
	}

	public function testErrorOnAdding() {
		$headers = (new Subscription\PreviewPage(
			new Uri\FakeUri('', '/subscription/5'),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->submitPreview($_POST)->headers();
		Assert::same(['Location' => '/subscription/5'], $headers);
	}
}

(new PreviewPage())->run();