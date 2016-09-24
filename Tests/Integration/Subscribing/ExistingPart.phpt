<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use Remembrall\Exception\NotFoundException;
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ExistingPart extends TestCase\Database {
	public function testUnknownPart() {
		Assert::exception(function() {
			(new Subscribing\ExistingPart(
                new Subscribing\FakePart(),
				123,
				$this->database
			))->content();
		}, NotFoundException::class, 'The part does not exist');
		Assert::exception(function() {
			(new Subscribing\ExistingPart(
				new Subscribing\FakePart(),
				124,
				$this->database
			))->refresh();
		}, NotFoundException::class, 'The part does not exist');
	}

	public function testExistingPart() {
		$this->database->query(
			"INSERT INTO parts (page_url, expression, content) VALUES
			('www.facedown.cz', '//d', 'd')"
		);
		Assert::noError(function() {
			(new Subscribing\ExistingPart(
				new Subscribing\FakePart('notEmpty'),
				1,
				$this->database
			))->content();
		});
		Assert::noError(function() {
			(new Subscribing\ExistingPart(
				new Subscribing\FakePart(),
				1,
				$this->database
			))->refresh();
		});
	}

	protected function prepareDatabase() {
		$this->purge(['parts']);
	}
}

(new ExistingPart)->run();
