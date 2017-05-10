<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Participants;

use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Page\Participants;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class InvitePage extends TestCase\Page {
	public function testBlockingGet() {
		$headers = (new Participants\InvitePage(
			new Uri\FakeUri(''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->response([])->headers();
		Assert::same(['Location' => '/error'], $headers);
	}
}

(new InvitePage())->run();