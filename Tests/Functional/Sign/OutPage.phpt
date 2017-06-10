<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Sign;

use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Page\Sign;
use Remembrall\Response;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class OutPage extends \Tester\TestCase {
	use TestCase\Page;

	public function testWorkingResponse() {
		$body = (new Sign\OutPage(
			new Uri\FakeUri(''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->response([])->body()->serialization();
		Assert::same('', $body);
	}

	public function testSuccessLeaving() {
		$_SESSION['id'] = 1;
		Assert::equal(
			new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl(new Uri\FakeUri(''), 'sign/in')
				),
				['success' => 'You have been logged out'],
				$_SESSION
			),
			(new Sign\OutPage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response([])
		);
	}

	public function testRedirectingToSamePageOnError() {
		Assert::equal(
			new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl(new Uri\FakeUri(''), 'sign/in')
				),
				['danger' => 'You are not logged in'],
				$_SESSION
			),
			(new Sign\OutPage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response([])
		);
	}
}

(new OutPage())->run();