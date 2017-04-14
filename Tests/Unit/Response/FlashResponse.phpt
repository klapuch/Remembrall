<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Response;

use Klapuch\UI;
use Remembrall\Response;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class FlashResponse extends Tester\TestCase {
	public function testWrappedMultipleMessages() {
		$_SESSION = [];
		(new UI\PersistentFlashMessage($_SESSION))->flash('a', 'success');
		(new UI\PersistentFlashMessage($_SESSION))->flash('b', 'danger');
		Assert::same(
			'<flashMessages><flashMessage><type>success</type><content>a</content></flashMessage><flashMessage><type>danger</type><content>b</content></flashMessage></flashMessages>',
			(new Response\FlashResponse())->body()->serialization()
		);
	}

	public function testEmptyElementOnNoAvailableMessage() {
		$_SESSION = [];
		Assert::same(
			'<flashMessages></flashMessages>',
			(new Response\FlashResponse())->body()->serialization()
		);
	}
}

(new FlashResponse())->run();