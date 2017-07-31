<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Subscription;

use Klapuch\Application;
use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Page\Subscription;
use Remembrall\Response;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class DeleteInteraction extends \Tester\TestCase {
	use TestCase\Page;

	public function testSuccessDeleting() {
		$this->database->exec(
			"INSERT INTO subscriptions (id, user_id, part_id, interval, last_update, snapshot) VALUES
			(1, 0, 4, 'PT3M', NOW(), '')"
		);
		$_POST['id'] = 1;
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri(''), 'subscriptions')
					),
					['success' => 'Subscription has been deleted'],
					$_SESSION
				)
			),
			(new Subscription\DeleteInteraction(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response($_POST)
		);
	}

	public function testErrorOnDeletingForeignOne() {
		$_POST['id'] = 1;
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri(''), 'subscriptions')
					),
					['danger' => 'You can not cancel foreign subscription'],
					$_SESSION
				)
			),
			(new Subscription\DeleteInteraction(
				new Uri\FakeUri(''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response($_POST)
		);
	}
}

(new DeleteInteraction())->run();