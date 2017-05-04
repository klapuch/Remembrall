<?php
declare(strict_types = 1);
namespace Remembrall\UI\Form\Subscription;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Uri;
use Remembrall\Form\Subscription;
use Spatie\Snapshots;

final class NewFormTest extends \PHPUnit\Framework\TestCase {
	use Snapshots\MatchesSnapshots;

	public function testOutput()
	{
		$this->assertMatchesXmlSnapshot(
			(new Subscription\NewForm(
				new Uri\FakeUri(''),
				new Csrf\FakeProtection('pr073ct10n'),
				new Form\EmptyStorage()
			))->render()
		);
	}
}