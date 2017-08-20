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
use Remembrall\Page\Subscription;
use Remembrall\Response;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class EditInteraction extends \Tester\TestCase {
	use TestCase\Page;

	public function testEditingSubscription() {
		$_POST['interval'] = '34';
		$_POST['id'] = 1;
		$_POST['act'] = 'Send';
		$user = (new Misc\TestUsers($this->database))->register();
		(new Misc\SamplePart($this->database))->try();
		(new Misc\SampleSubscription($this->database, $user, 1))->try();
		$_SESSION['id'] = $user->id();
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
			))->template($_POST)
		);
	}

	public function testErrorOnEditingForeignSubscription() {
		$_POST['interval'] = '34';
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
			))->template($_POST)
		);
	}
}

(new EditInteraction())->run();