<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use Remembrall\Exception;
use Remembrall\Model\{
	Access, Subscribing
};
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ExistingPart extends TestCase\Database {
	public function testUnknownPart() {
		Assert::exception(function() {
			(new Subscribing\ExistingPart(
				new Subscribing\FakePart(),
				'www.facedown.cz',
				'//xxxx',
				$this->database
			))->content();
		}, Exception\NotFoundException::class, 'The part does not exist');
		Assert::exception(function() {
			(new Subscribing\ExistingPart(
				new Subscribing\FakePart(),
				'www.facedown.cz',
				'//xxxx',
				$this->database
			))->refresh();
		}, Exception\NotFoundException::class, 'The part does not exist');
	}

	public function testExistingPart() {
		Assert::noError(function() {
			(new Subscribing\ExistingPart(
				new Subscribing\FakePart('notEmpty'),
				'www.facedown.cz',
				'//d',
				$this->database
			))->content();
		});
		Assert::noError(function() {
			(new Subscribing\ExistingPart(
				new Subscribing\FakePart(),
				'www.facedown.cz',
				'//d',
				$this->database
			))->refresh();
		});
	}

	protected function prepareDatabase() {
		$this->purge(['parts']);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content, content_hash) VALUES
			("www.facedown.cz", "//d", "d", MD5("d"))'
		);
	}
}

(new ExistingPart)->run();
