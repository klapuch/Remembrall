<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Subscription;

use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Misc;
use Remembrall\Page\Subscription;
use Remembrall\TestCase;
use Tester\Assert;
use Tester\DomQuery;

require __DIR__ . '/../../bootstrap.php';

final class EditPage extends \Tester\TestCase {
	use TestCase\Page;

	public function testWorkingResponseForOwnedSubscription() {
		$user = (new Misc\TestUsers($this->database))->register();
		$this->database->exec(
			"INSERT INTO subscriptions (id, user_id, part_id, interval, last_update, snapshot) VALUES
			(1, {$user->id()}, 4, 'PT3M', NOW(), '')"
		);
		$_SESSION['id'] = $user->id();
		Assert::same(
			'Edit subscription',
			(string) DomQuery::fromHtml(
				(new Misc\TestTemplate(
					(new Subscription\EditPage(
						new Uri\FakeUri('', '/subscription/edit/1'),
						new Log\FakeLogs(),
						new Ini\FakeSource($this->configuration)
					))->response(['id' => 1])
				))->render()
			)->find('h1')[0]
		);
	}
}

(new EditPage())->run();