<?php
declare(strict_types = 1);
namespace Remembrall\Snapshot\Form\Participants;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Output;
use Klapuch\Uri;
use Remembrall\Form\Participants;
use Remembrall\Model\Subscribing;
use Spatie\Snapshots;

final class InviteFormTest extends \PHPUnit\Framework\TestCase {
	use Snapshots\MatchesSnapshots;

	public function testOutput()
	{
		$this->assertMatchesXmlSnapshot(
			(new Participants\InviteForm(
				new Subscribing\FakeSubscription(
					null,
					new Output\Xml(['id' => 666], 'root')
				),
				new Uri\FakeUri(''),
				new Csrf\FakeProtection('pr073ct10n'),
				new Form\EmptyStorage()
			))->render()
		);
	}
}