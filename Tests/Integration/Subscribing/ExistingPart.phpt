<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use Remembrall\Exception;
use Remembrall\Model\{
	Subscribing
};
use Remembrall\TestCase;
use Tester\Assert;
use Klapuch\Uri;

require __DIR__ . '/../../bootstrap.php';

final class ExistingPart extends TestCase\Database {
	public function testUnknownPart() {
		Assert::exception(function() {
			(new Subscribing\ExistingPart(
                new Subscribing\FakePart(),
                new Uri\FakeUri('www.facedown.cz'),
				'//xxxx',
				$this->database
			))->content();
		}, Exception\NotFoundException::class, 'The part does not exist');
		Assert::exception(function() {
			(new Subscribing\ExistingPart(
				new Subscribing\FakePart(),
                new Uri\FakeUri('www.facedown.cz'),
				'//xxxx',
				$this->database
			))->refresh();
		}, Exception\NotFoundException::class, 'The part does not exist');
	}

	public function testExistingPart() {
		$this->database->query(
			"INSERT INTO parts (page_url, expression, content) VALUES
			('www.facedown.cz', '//d', 'd')"
		);
		Assert::noError(function() {
			(new Subscribing\ExistingPart(
				new Subscribing\FakePart('notEmpty'),
                new Uri\FakeUri('www.facedown.cz'),
				'//d',
				$this->database
			))->content();
		});
		Assert::noError(function() {
			(new Subscribing\ExistingPart(
				new Subscribing\FakePart(),
                new Uri\FakeUri('www.facedown.cz'),
				'//d',
				$this->database
			))->refresh();
		});
	}

	protected function prepareDatabase() {
		$this->purge(['parts']);
	}
}

(new ExistingPart)->run();
