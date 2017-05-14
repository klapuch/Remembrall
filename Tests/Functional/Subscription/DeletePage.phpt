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

final class DeletePage extends TestCase\Page {
	public function testBlockingGet() {
		$headers = (new Subscription\DeletePage(
			new Uri\FakeUri(''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->response([])->headers();
		Assert::same(['Location' => '/error'], $headers);
	}

	public function testDeleting() {
		$_POST['id'] = 123;
		$headers = (new Subscription\DeletePage(
			new Uri\FakeUri(''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->submitDelete($_POST)->headers();
		Assert::same(['Location' => '/subscriptions'], $headers);
	}
}

(new DeletePage())->run();