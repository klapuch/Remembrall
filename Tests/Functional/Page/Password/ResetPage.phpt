<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Page\Password;

use Klapuch\Application;
use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Misc;
use Remembrall\Page\Password;
use Remembrall\Response;
use Remembrall\TestCase;
use Tester\Assert;
use Tester\DomQuery;

require __DIR__ . '/../../../bootstrap.php';

final class ResetPage extends \Tester\TestCase {
	use TestCase\Page;

	public function testWorkingRenderingForValidReminder() {
		$reminder = '123abc123';
		$statement = $this->database->prepare(
			"INSERT INTO forgotten_passwords (user_id, used, reminder, reminded_at, expire_at) VALUES
            (1, FALSE, ?, NOW(), NOW() + INTERVAL '10 MINUTE')"
		);
		$statement->execute([$reminder]);
		Assert::same(
			'Password reset',
			(string) DomQuery::fromHtml(
				(new Misc\TestTemplate(
					(new Password\ResetPage(
						new Uri\FakeUri('', '/password/reset/123'),
						new Log\FakeLogs(),
						new Ini\FakeSource($this->configuration)
					))->response(['reminder' => $reminder])
				))->render()
			)->find('h1')[0]
		);
	}

	public function testRedirectForInvalidReminder() {
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri(''), 'password/remind')
					),
					['danger' => 'Reminder is no longer valid.'],
					$_SESSION
				)
			),
			(new Password\ResetPage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response(['reminder' => 'abc123'])
		);
	}
}

(new ResetPage())->run();