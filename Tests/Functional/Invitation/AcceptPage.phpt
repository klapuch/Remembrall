<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Invitation;

use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Page\Invitation;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class AcceptPage extends TestCase\Page {
	public function testWorkingResponse() {
		$body = (new Invitation\AcceptPage(
			new Uri\FakeUri(''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->response(['code' => 'abc123'])->body()->serialization();
		Assert::same('', $body);
	}

	public function testRedirectInEveryCase() {
		$headers = (new Invitation\AcceptPage(
			new Uri\FakeUri(''),
			new Log\FakeLogs(),
			new Ini\FakeSource($this->configuration)
		))->response(['code' => 'abc123'])->headers();
		Assert::same(['Location' => '/sign/in'], $headers);
	}
}

(new AcceptPage())->run();