<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\UI\Form\Verification;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Snappie;
use Klapuch\Uri;
use Remembrall\Form\Verification;

require __DIR__ . '/../../../bootstrap.php';

final class RequestForm extends \Tester\TestCase {
	use Snappie\Assertions;

	public function testOutput() {
		$this->assertXml(
			(new Verification\RequestForm(
				new Uri\FakeUri(''),
				new Csrf\FakeProtection('pr073ct10n'),
				new Form\EmptyStorage()
			))->render()
		);
	}
}

(new RequestForm())->run();