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
use Remembrall\Misc;
use Remembrall\Model\Web\FakePart;
use Remembrall\Model\Web\TemporaryParts;
use Remembrall\Page\Subscription;
use Remembrall\Response;
use Remembrall\TestCase;
use Tester\Assert;
use Tester\DomQuery;

require __DIR__ . '/../../../bootstrap.php';

final class PreviewPage extends \Tester\TestCase {
	use TestCase\Page;

	public function testWorkingRendering() {
		$_SESSION['part'] = ['url' => 'http://www.example.com', 'expression' => '//h1', 'language' => 'xpath'];
		(new TemporaryParts(
			$this->redis
		))->add(
			new FakePart(''),
			new Uri\FakeUri($_SESSION['part']['url']),
			$_SESSION['part']['expression'],
			$_SESSION['part']['language']
		);
		$user = (new Misc\TestUsers($this->database))->register();
		(new Misc\SamplePart($this->database))->try();
		(new Misc\SampleSubscription($this->database, $user, 1))->try();
		$_SESSION['id'] = $user->id();
		Assert::contains(
			'Preview of ',
			(string) DomQuery::fromHtml(
				(new Misc\TestTemplate(
					(new Subscription\PreviewPage(
						new Uri\FakeUri('', '/subscription/preview'),
						new Log\FakeLogs(),
						new Ini\FakeSource($this->configuration)
					))->template([])
				))->render()
			)->find('h1')[0]
		);
	}

	public function testErrorOnNotFoundPart() {
		$_SESSION['part'] = ['url' => 'http://www.example.com', 'expression' => '//h1', 'language' => 'xpath'];
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri(''), 'subscription')
					),
					['danger' => 'Part not found'],
					$_SESSION
				)
			),
			(new Subscription\PreviewPage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->template([])
		);
	}

	public function testMissingSessionFieldForResponseLeadingToError() {
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri(''), 'subscription')
					),
					['danger' => 'Missing referenced part'],
					$_SESSION
				)
			),
			(new Subscription\PreviewPage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->template([])
		);
	}

	public function testMissingSomeSessionFieldForResponseLeadingToError() {
		$_SESSION['part'] = ['language' => 'xpath'];
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri(''), 'subscription')
					),
					['danger' => 'Missing referenced part'],
					$_SESSION
				)
			),
			(new Subscription\PreviewPage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->template([])
		);
	}
}

(new PreviewPage())->run();