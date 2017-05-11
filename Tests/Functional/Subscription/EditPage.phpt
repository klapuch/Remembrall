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
use Remembrall\Page\Subscription;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class EditPage extends TestCase\Page {
	/**
	 * @throws \Remembrall\Exception\NotFoundException You can not see foreign subscription
	 */
	public function testThrowingOnForeignSubscription() {
		(new Subscription\EditPage(
			new Uri\FakeUri('', ''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->response(['id' => 0])->body();
	}

	public function testWorkingResponseForOwnedSubscription() {
		$this->purge(['subscriptions']);
		$this->database->exec(
			"INSERT INTO subscriptions (id, user_id, part_id, interval, last_update, snapshot) VALUES
			(1, 0, 4, 'PT3M', NOW(), '')"
		);
		Assert::noError(function() {
			$body = (new Subscription\EditPage(
				new Uri\FakeUri('', ''),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->response(['id' => 1])->body()->serialization();
			$dom = new \DOMDocument();
			$dom->loadXML($body);
		});
	}
}

(new EditPage())->run();