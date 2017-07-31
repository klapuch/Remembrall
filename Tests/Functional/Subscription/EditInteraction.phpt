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

final class EditInteraction extends \Tester\TestCase {
	use TestCase\Page;

	public function testEditingSubscription() {
		$_POST['interval'] = 34;
		$_POST['id'] = 1;
		$_POST['act'] = 'Send';
		$this->database->exec(
			"INSERT INTO subscriptions (id, user_id, part_id, interval, last_update, snapshot) VALUES
			(1, 0, 4, 'PT3M', NOW(), '')"
		);
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri('', ''), 'subscriptions')
					),
					['success' => 'Subscription has been edited'],
					$_SESSION
				)
			),
			(new Subscription\EditInteraction(
				new Uri\FakeUri('', ''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response($_POST)
		);
	}

	public function testErrorOnEditingForeignSubscription() {
		$_POST['interval'] = 34;
		$_POST['id'] = 1;
		$_POST['act'] = 'Send';
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\RelativeUrl(new Uri\FakeUri('', 'subscription/1'), 'subscription/1')
					),
					['danger' => 'You can not edit foreign subscription'],
					$_SESSION
				)
			),
			(new Subscription\EditInteraction(
				new Uri\FakeUri('', 'subscription/1'),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response($_POST)
		);
	}
}

(new EditInteraction())->run();