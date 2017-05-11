<?php
declare(strict_types = 1);
namespace Remembrall\UI\Form\Subscription;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Uri;
use Remembrall\Form\Subscription;
use Spatie\Snapshots;

final class EditFormTest extends \PHPUnit\Framework\TestCase {
	use Snapshots\MatchesSnapshots;

	public function testOutput() {
		$dom = new \DOMDocument();
		$dom->loadXML(
			'<root>
				<url>www.keybase.com</url>
				<expression>//expr</expression>
				<interval>44</interval>
			</root>'
		);
		$this->assertMatchesXmlSnapshot(
			(new Subscription\EditForm(
				$dom,
				new Uri\FakeUri('', ''),
				new Csrf\FakeProtection('pr073ct10n'),
				new Form\EmptyStorage()
			))->render()
		);
	}
}