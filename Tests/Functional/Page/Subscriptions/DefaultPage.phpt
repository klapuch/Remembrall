<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Page\Subscriptions;

use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Misc;
use Remembrall\Page\Subscriptions;
use Remembrall\TestCase;
use Tester\Assert;
use Tester\DomQuery;

require __DIR__ . '/../../../bootstrap.php';

final class DefaultPage extends \Tester\TestCase {
	use TestCase\Page;

	public function testWorkingRenderingOnSomeSubscriptions() {
		$user = (new Misc\TestUsers($this->database))->register();
		(new Misc\SamplePart($this->database))->try();
		(new Misc\SampleSubscription($this->database, ['user' => $user->id(), 'part' => 1]))->try();
		$_SESSION['id'] = $user->id();
		Assert::same(
			'Subscriptions',
			(string) DomQuery::fromHtml(
				(new Misc\TestTemplate(
					(new Subscriptions\DefaultPage(
						new Uri\FakeUri('', '/subscriptions'),
						new Log\FakeLogs(),
						new Ini\FakeSource($this->configuration)
					))->template([])
				))->render()
			)->find('h1')[0]
		);
	}

	public function testWorkingRenderingOnNoSubscriptions() {
		$_SESSION['id'] = (new Misc\TestUsers($this->database))->register()->id();
		Assert::noError(function() {
			(new Misc\TestTemplate(
				(new Subscriptions\DefaultPage(
					new Uri\FakeUri('', '/subscriptions'),
					new Log\FakeLogs(),
					new Ini\FakeSource($this->configuration)
				))->template([])
			))->render();
		});
	}
}

(new DefaultPage())->run();