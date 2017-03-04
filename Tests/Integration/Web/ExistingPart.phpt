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

final class ExistingPart extends TestCase\Database {
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
			'The part does not exist'
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
			'The part does not exist'
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
			'The part does not exist'
		);
	}

	public function testExistingPart() {
		$this->database->exec(
			"INSERT INTO parts (page_url, expression, content, snapshot) VALUES
			('www.facedown.cz', '//d', 'd', '')"
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

	protected function prepareDatabase() {
		$this->purge(['parts']);
	}
}

(new ExistingPart)->run();