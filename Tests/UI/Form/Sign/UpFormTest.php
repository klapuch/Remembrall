<?php
declare(strict_types = 1);
namespace Remembrall\UI\Form\Sign;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Uri;
use Remembrall\Form\Sign;
use Spatie\Snapshots;

final class UpFormTest extends \PHPUnit\Framework\TestCase {
	use Snapshots\MatchesSnapshots;

	public function testOutput()
	{
		$this->assertMatchesXmlSnapshot(
			(new Sign\UpForm(
				new Uri\FakeUri(''),
				new Csrf\FakeProtection('pr073ct10n'),
				new Form\EmptyStorage()
			))->render()
		);
	}
}