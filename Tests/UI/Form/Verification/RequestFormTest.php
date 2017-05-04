<?php
declare(strict_types = 1);
namespace Remembrall\UI\Form\Verification;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Uri;
use Remembrall\Form\Verification;
use Spatie\Snapshots;

final class RequestFormTest extends \PHPUnit\Framework\TestCase {
	use Snapshots\MatchesSnapshots;

	public function testOutput()
	{
		$this->assertMatchesXmlSnapshot(
			(new Verification\RequestForm(
				new Uri\FakeUri(''),
				new Csrf\FakeProtection('pr073ct10n'),
				new Form\EmptyStorage()
			))->render()
		);
	}
}