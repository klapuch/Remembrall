<?php
declare(strict_types = 1);
namespace Remembrall\UI\Form\Subscription;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Output;
use Klapuch\Uri;
use Remembrall\Form\Subscription;
use Remembrall\Model\Subscribing;
use Spatie\Snapshots;

final class EditFormTest extends \PHPUnit\Framework\TestCase {
	use Snapshots\MatchesSnapshots;

	public function testOutput() {
		$this->assertMatchesXmlSnapshot(
			(new Subscription\EditForm(
				new Subscribing\FakeSubscription(
					null,
					new Output\Xml(
						[
							'url' => 'www.keybase.com',
							'expression' => '//expr',
							'interval' => 44,
						],
						'root'
					)
				),
				new Uri\FakeUri('', ''),
				new Csrf\FakeProtection('pr073ct10n'),
				new Form\EmptyStorage()
			))->render()
		);
	}
}