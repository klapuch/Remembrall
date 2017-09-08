<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\UI\Form\Subscription;

use Klapuch\Csrf;
use Klapuch\Form;
use Klapuch\Snappie;
use Klapuch\Uri;
use Remembrall\Form\Subscription;

require __DIR__ . '/../../../bootstrap.php';

final class PreviewForm extends \Tester\TestCase {
	use Snappie\Assertions;

	public function testOutput() {
		$this->assertXml(
			(new Subscription\PreviewForm(
				new Uri\FakeUri(''),
				new Csrf\FakeProtection('pr073ct10n'),
				new Form\EmptyStorage()
			))->render()
		);
	}
}

(new PreviewForm())->run();