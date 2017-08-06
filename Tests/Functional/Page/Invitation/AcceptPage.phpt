<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Page\Invitation;

use Klapuch\Application;
use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Page\Invitation;
use Remembrall\Response;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class AcceptPage extends \Tester\TestCase {
	use TestCase\Page;

	public function testSuccessAccepting() {
		$code = 'abc123';
		$this->database->exec(
			"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) 
			VALUES ('foo@email.cz', 1, '{$code}', NOW(), FALSE, NULL)"
		);
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri(''), 'sign/in')
					),
					['success' => 'Invitation has been accepted'],
					$_SESSION
				)
			),
			(new Invitation\AcceptPage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response(['code' => $code])
		);
	}

	public function testErrorOnAccepting() {
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri(''), 'sign/in')
					),
					['danger' => 'The invitation with code "abc123" is accepted or does not exist'],
					$_SESSION
				)
			),
			(new Invitation\AcceptPage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response(['code' => 'abc123'])
		);
	}
}

(new AcceptPage())->run();