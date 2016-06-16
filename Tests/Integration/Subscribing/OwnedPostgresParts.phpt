<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use Dibi;
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;
use Nette\Security;

require __DIR__ . '/../../bootstrap.php';

final class OwnedPostgresParts extends TestCase\Database {
    public function testSubscribingBrandNew() {
        (new Subscribing\OwnedPostgresParts(
            $this->database,
            new Subscribing\FakePage('www.google.com'),
            new Security\Identity(666)
        ))->subscribe(
            new Subscribing\FakePart(
                '<p>Content</p>',
                null,
                false,
                new Subscribing\FakeExpression('//p')
            ),
            new Subscribing\FakeInterval(
                new \DateTimeImmutable('2000-01-01 01:01:01'),
                null,
                new \DateInterval('PT158M')
            )
        );
		$rows = $this->database->fetchAll(
			'SELECT ID, url, content, expression, `interval`, visited_at FROM parts'
		);
		Assert::same(1, count($rows));
		$row = current($rows);
		Assert::same(1, $row['ID']);
		Assert::same('www.google.com', $row['url']);
		Assert::same('<p>Content</p>', $row['content']);
		Assert::same('//p', $row['expression']);
		Assert::same(158, $row['interval']);
		Assert::same('2000-01-01 01:01:01', (string)$row['visited_at']);
    }

	public function testSubscribingDuplication() {
		$parts = new Subscribing\OwnedPostgresParts(
			$this->database,
			new Subscribing\FakePage('www.google.com'),
			new Security\Identity(666)
		);
		$parts->subscribe(
			new Subscribing\FakePart(
				'<p>Content</p>',
				null,
				false,
				new Subscribing\FakeExpression('//p')
			),
			new Subscribing\FakeInterval(
				new \DateTimeImmutable('2000-01-01 01:01:01'),
				null,
				new \DateInterval('PT2M')
			)
		);
		Assert::exception(function() use($parts) {
			$parts->subscribe(
				new Subscribing\FakePart(
					'<p>Content</p>',
					null,
					false,
					new Subscribing\FakeExpression('//p')
				),
				new Subscribing\FakeInterval(
					new \DateTimeImmutable('2000-01-01 01:01:01'),
					null,
					new \DateInterval('PT2M')
				)
			);
		}, 'Remembrall\Exception\DuplicateException');
	}

	public function testIteratingOwnedPartsOnConcretePage() {
		$this->database->query(
			'INSERT INTO parts (url, expression, content, visited_at, `interval`, subscriber_id) VALUES
			("a", "//a", "a", NOW(), 1, 1)'
		);
		$this->database->query(
			'INSERT INTO parts (url, expression, content, visited_at, `interval`, subscriber_id) VALUES
			("b", "//b", "b", NOW(), 2, 2)'
		);
		$this->database->query(
			'INSERT INTO parts (url, expression, content, visited_at, `interval`, subscriber_id) VALUES
			("c", "//c", "c", NOW(), 3, 1)'
		);
		$this->database->query(
			'INSERT INTO parts (url, expression, content, visited_at, `interval`, subscriber_id) VALUES
			("a", "//d", "d", NOW(), 4, 1)'
		);
		$parts = (new Subscribing\OwnedPostgresParts(
			$this->database,
			new Subscribing\FakePage('a'),
			new Security\Identity(1)
		))->iterate();
		Assert::same(2, count($parts));
		Assert::same('//a', (string)$parts[0]->expression());
		Assert::same('//d', (string)$parts[1]->expression());
	}

    protected function prepareDatabase() {
        $this->database->query('TRUNCATE parts');
    }
}

(new OwnedPostgresParts)->run();
