<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\UI\Form\Sign;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Snappie;
use Klapuch\Uri;
use Remembrall\Form\Sign;

require __DIR__ . '/../../../bootstrap.php';

final class InForm extends \Tester\TestCase {
	use Snappie\Assertions;

	public function testOutput() {
		$this->assertXml(
			(new Sign\InForm(
				new Uri\FakeUri(''),
				new Csrf\FakeProtection('pr073ct10n'),
				new Form\EmptyStorage()
			))->render()
		);
	}
}

(new InForm())->run();