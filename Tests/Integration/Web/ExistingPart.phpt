<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Web;

use Remembrall\Model\Web;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ExistingPart extends \Tester\TestCase {
	use TestCase\Database;

	public function testThrowingOnUnknownPart() {
		Assert::exception(
			function() {
				(new Web\ExistingPart(
					new Web\FakePart(),
					123,
					$this->database
				))->content();
			},
			\Remembrall\Exception\NotFoundException::class,
			'Content from part id "123" does not exist'
		);
		Assert::exception(
			function() {
				(new Web\ExistingPart(
					new Web\FakePart(),
					124,
					$this->database
				))->refresh();
			},
			\Remembrall\Exception\NotFoundException::class,
			'The part id "124" does not exist'
		);
		Assert::exception(
			function() {
				(new Web\ExistingPart(
					new Web\FakePart(),
					125,
					$this->database
				))->snapshot();
			},
			\Remembrall\Exception\NotFoundException::class,
			'Snapshot from part id "125" does not exist'
		);
	}

	public function testExistingPart() {
		$this->database->exec(
			"INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES
			(1, 'www.facedown.cz', '//d', 'd', '')"
		);
		Assert::noError(
			function() {
				(new Web\ExistingPart(
					new Web\FakePart('notEmpty'),
					1,
					$this->database
				))->content();
			}
		);
		Assert::noError(
			function() {
				(new Web\ExistingPart(
					new Web\FakePart(),
					1,
					$this->database
				))->refresh();
			}
		);
		Assert::noError(
			function() {
				(new Web\ExistingPart(
					new Web\FakePart('notEmpty', null, 'snap'),
					1,
					$this->database
				))->snapshot();
			}
		);
	}
}

(new ExistingPart)->run();