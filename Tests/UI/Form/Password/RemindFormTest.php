<?php
declare(strict_types = 1);
namespace Remembrall\UI\Form\Password;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Uri;
use Remembrall\Form\Password;
use Spatie\Snapshots;

final class RemindFormTest extends \PHPUnit\Framework\TestCase {
	use Snapshots\MatchesSnapshots;

	public function testOutput() {
		$this->assertMatchesXmlSnapshot(
			(new Password\RemindForm(
				new Uri\FakeUri(''),
				new Csrf\FakeProtection('pr073ct10n'),
				new Form\EmptyStorage()
			))->render()
		);
	}
}