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
	public function testErrorOnForeignSubscription() {
		$this->purge(['users', 'verification_codes']);
		$this->database->exec(
			"INSERT INTO users (id, email, password, role) VALUES
			(2, 'klapuchdominik@gmail.com', 'dc98d5af8f15840afcab387d5923f330df4a7bc76625e024fec2cb1f626543dccf352999ffd4e3c15047bee301104d06651ccaaee60ed3b98723b1e04cbaa429e00f088976bd9b5a94d5863f1d124ee8', 'member')"
		);
		$this->database->exec(
			"INSERT INTO verification_codes (user_id, code, used, used_at) VALUES
			(2, 'c7fb39e3b3e0d9efa6fce134b703fcea5c4c4196cef0dcaccf:3b59944087428cd5b95be4f18dcf06b8815b9fa6', TRUE, NOW());"
		);
		$_SESSION['role'] = 'member';
		$_SESSION['id'] = 2;
		$headers = (new Subscription\EditPage(
			new Uri\FakeUri('', 'subscription/edit/5'),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->response(['id' => 5])->headers();
		Assert::same('/subscriptions', $headers['Location']);
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

	public function testEditingSubscription() {
		$_POST['interval'] = 34;
		$_POST['act'] = 'Send';
		$this->purge(['subscriptions']);
		$this->database->exec(
			"INSERT INTO subscriptions (id, user_id, part_id, interval, last_update, snapshot) VALUES
			(1, 0, 4, 'PT3M', NOW(), '')"
		);
		$headers = (new Subscription\EditPage(
			new Uri\FakeUri('', ''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->submitEdit($_POST, ['id' => 1])->headers();
		Assert::same(['Location' => '/subscriptions'], $headers);
	}

	public function testErrorOnEditing() {
		$headers = (new Subscription\EditPage(
			new Uri\FakeUri('', 'subscription/1'),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->submitEdit($_POST, ['id' => 1])->headers();
		Assert::same(['Location' => '/subscription/1'], $headers);
	}
}

(new EditPage())->run();