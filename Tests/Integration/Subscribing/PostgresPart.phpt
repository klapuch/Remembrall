<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use Remembrall\Model\{
	Subscribing
};
use Remembrall\TestCase;
use Tester\Assert;
use Klapuch\Uri;

require __DIR__ . '/../../bootstrap.php';

final class PostgresPart extends TestCase\Database {
	public function testContent() {
		Assert::same(
			'd',
			(new Subscribing\PostgresPart(
                new Subscribing\FakePart(),
                new Uri\FakeUri('www.facedown.cz'),
				'//d',
				$this->database
			))->content()
		);
	}

	public function testRefreshingPart() {
		(new Subscribing\PostgresPart(
			new Subscribing\FakePart('NEW_CONTENT'),
            new Uri\FakeUri('www.facedown.cz'),
			'//d',
			$this->database
		))->refresh();
		$parts = $this->database->fetchAll('SELECT * FROM parts');
		Assert::count(1, $parts);
		Assert::same('NEW_CONTENT', $parts[0]['content']);
	}

	protected function prepareDatabase() {
		$this->purge(['parts']);
		$this->database->query(
			"INSERT INTO parts (page_url, expression, content) VALUES
			('www.facedown.cz', '//d', 'd')"
		);
	}
}

(new PostgresPart)->run();
