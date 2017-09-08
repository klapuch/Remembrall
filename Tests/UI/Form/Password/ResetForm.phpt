<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\UI\Form\Password;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Snappie;
use Klapuch\Uri;
use Remembrall\Form\Password;

require __DIR__ . '/../../../bootstrap.php';

final class ResetForm extends \Tester\TestCase {
	use Snappie\Assertions;

	public function testOutput() {
		$this->assertXml(
			(new Password\ResetForm(
				'123reminder123',
				new Uri\FakeUri('', ''),
				new Csrf\FakeProtection('pr073ct10n'),
				new Form\EmptyStorage()
			))->render()
		);
	}
}

(new ResetForm())->run();