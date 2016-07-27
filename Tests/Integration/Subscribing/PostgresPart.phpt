<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use Remembrall\Model\{
	Access, Subscribing
};
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class PostgresPart extends TestCase\Database {
	public function testContent() {
		Assert::same(
			'd',
			(new Subscribing\PostgresPart(
				new Subscribing\FakePart(),
				'www.facedown.cz',
				'//d',
				$this->database,
				new Access\FakeSubscriber()
			))->content()
		);
	}

	public function testDifferentParts() {
		Assert::false(
			(new Subscribing\PostgresPart(
				new Subscribing\FakePart(),
				'www.facedown.cz',
				'//d',
				$this->database,
				new Access\FakeSubscriber()
			))->equals(
				new Subscribing\FakePart('<p>abc</p>')
			)
		);
	}

	public function testEquivalentParts() {
		Assert::true(
			(new Subscribing\PostgresPart(
				new Subscribing\FakePart(),
				'www.facedown.cz',
				'//d',
				$this->database,
				new Access\FakeSubscriber()
			))->equals(
				new Subscribing\FakePart('d')
			)
		);
	}

	public function testRefreshingPart() {
		(new Subscribing\PostgresPart(
			new Subscribing\FakePart('NEW_CONTENT'),
			'www.facedown.cz',
			'//d',
			$this->database,
			new Access\FakeSubscriber()
		))->refresh();
		$parts = $this->database->fetchAll('SELECT * FROM parts');
		Assert::count(1, $parts);
		Assert::same('NEW_CONTENT', $parts[0]['content']);
	}

	protected function prepareDatabase() {
		$this->purge(['parts']);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.facedown.cz", "//d", "d")'
		);
	}
}

(new PostgresPart)->run();
