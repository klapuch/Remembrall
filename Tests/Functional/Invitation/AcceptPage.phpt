<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Invitation;

use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Page\Invitation;
use Remembrall\Response;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class AcceptPage extends \Tester\TestCase {
	use TestCase\Page;

	public function testWorkingResponse() {
		$body = (new Invitation\AcceptPage(
			new Uri\FakeUri(''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->response(['code' => 'abc123'])->body()->serialization();
		Assert::same('', $body);
	}

	public function testSuccessAccepting() {
		$code = 'abc123';
		$this->database->exec(
			"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) 
			VALUES ('foo@email.cz', 1, '{$code}', NOW(), FALSE, NULL)"
		);
		$response = (new Invitation\AcceptPage(
			new Uri\FakeUri(''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->response(['code' => $code]);
		Assert::equal(
			new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl(new Uri\FakeUri(''), 'sign/in')
				),
				['success' => 'Invitation has been accepted'],
				$_SESSION
			),
			$response
		);
	}

	public function testErrorOnAccepting() {
		$response = (new Invitation\AcceptPage(
			new Uri\FakeUri(''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->response(['code' => 'abc123']);
		Assert::equal(
			new Response\InformativeResponse(
				new Response\RedirectResponse(
					new Response\EmptyResponse(),
					new Uri\RelativeUrl(new Uri\FakeUri(''), 'sign/in')
				),
				['danger' => 'The invitation with code "abc123" is accepted or does not exist'],
				$_SESSION
			),
			$response
		);
	}
}

(new AcceptPage())->run();