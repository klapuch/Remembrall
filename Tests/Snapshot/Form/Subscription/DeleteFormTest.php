<?php
declare(strict_types = 1);
namespace Remembrall\Snapshot\Form\Subscription;

use Klapuch\Csrf;
use Klapuch\Output;
use Klapuch\Uri;
use Remembrall\Form\Subscription;
use Remembrall\Model\Subscribing;
use Spatie\Snapshots;

final class DeleteFormTest extends \PHPUnit\Framework\TestCase {
	use Snapshots\MatchesSnapshots;

	public function testOutput()
	{
		$this->assertMatchesXmlSnapshot(
			(new Subscription\DeleteForm(
				new Subscribing\FakeSubscription(
					null,
					new Output\Xml(['id' => 666], 'root')
				),
				new Uri\FakeUri(''),
				new Csrf\FakeProtection('pr073ct10n')
			))->render()
		);
	}
}