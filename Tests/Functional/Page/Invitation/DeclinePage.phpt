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
use Remembrall\Misc;
use Remembrall\Page\Invitation;
use Remembrall\Response;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class DeclinePage extends \Tester\TestCase {
	use TestCase\Page;

	public function testSuccessDeclining() {
		$code = 'abc123';
		(new Misc\SampleParticipant(
			$this->database,
			['code' => $code, 'subscription' => 1, 'decided_at' => 'NULL']
		))->try();
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri(''), 'sign/in')
					),
					['success' => 'Invitation has been declined'],
					$_SESSION
				)
			),
			(new Invitation\DeclinePage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->template(['code' => $code])
		);
	}

	public function testErrorOnDeclining() {
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri(''), 'sign/in')
					),
					['danger' => 'The invitation with code "abc123" is declined or does not exist'],
					$_SESSION
				)
			),
			(new Invitation\DeclinePage(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->template(['code' => 'abc123'])
		);
	}
}

(new DeclinePage())->run();