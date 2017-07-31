<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Subscriptions;

use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Misc;
use Remembrall\Page\Subscriptions;
use Remembrall\TestCase;
use Tester\Assert;
use Tester\DomQuery;

require __DIR__ . '/../../bootstrap.php';

final class DefaultPage extends \Tester\TestCase {
	use TestCase\Page;

	public function testWorkingRenderingOnSomeSubscriptions() {
		$this->database->exec(
			"INSERT INTO subscriptions (id, user_id, part_id, interval, last_update, snapshot) VALUES
			(1, 1, 4, 'PT3M', NOW(), '')"
		);
		$_SESSION['id'] = (new Misc\TestUsers($this->database))->register()->id();
		Assert::same(
			'Subscriptions',
			(string) DomQuery::fromHtml(
				(new Subscriptions\DefaultPage(
					new Uri\FakeUri('', '/subscriptions'),
					new Log\FakeLogs(),
					new Ini\FakeSource($this->configuration)
				))->response([])->render(['nonce' => '', 'base_url' => ''])
			)->find('h1')[0]
		);
	}

	public function testWorkingRenderingOnNoSubscriptions() {
		$_SESSION['id'] = (new Misc\TestUsers($this->database))->register()->id();
		Assert::noError(function() {
			(new Subscriptions\DefaultPage(
				new Uri\FakeUri('', '/subscriptions'),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response([])->render(['nonce' => '', 'base_url' => '']);
		});
	}
}

(new DefaultPage())->run();