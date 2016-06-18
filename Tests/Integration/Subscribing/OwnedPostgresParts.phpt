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
		$parts = $this->database->fetchAll(
			'SELECT ID, page_id, content, expression, `interval`
			FROM parts'
		);
		Assert::count(1, $parts);
		$part = current($parts);
		Assert::same(1, $part['ID']);
		Assert::same(1, $part['page_id']);
		Assert::same('<p>Content</p>', $part['content']);
		Assert::same('//p', $part['expression']);
		Assert::same(158, $part['interval']);
		$partVisits = $this->database->fetchAll('SELECT part_id FROM part_visits');
		Assert::count(1, $partVisits);
		$partVisit = current($partVisits);
		Assert::same(1, $partVisit['part_id']);
    }

	public function testSubscribingDuplicationWithRollback() {
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
		Assert::count(1, $this->database->fetchAll('SELECT ID FROM parts'));
		Assert::count(1, $this->database->fetchAll('SELECT ID FROM part_visits'));
	}

	public function testIteratingOwnedPartsOnConcretePage() {
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content, `interval`, subscriber_id) VALUES
			(1, "//a", "a", 1, 1)'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content, `interval`, subscriber_id) VALUES
			(2, "//b", "b", 2, 2)'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content, `interval`, subscriber_id) VALUES
			(2, "//c", "c", 3, 1)'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content, `interval`, subscriber_id) VALUES
			(1, "//d", "d", 4, 1)'
		);
		$parts = (new Subscribing\OwnedPostgresParts(
			$this->database,
			new Subscribing\FakePage('www.google.com'),
			new Security\Identity(1)
		))->iterate();
		Assert::count(2, $parts);
		Assert::same('//a', (string)$parts[0]->expression());
		Assert::same('//d', (string)$parts[1]->expression());
	}

    protected function prepareDatabase() {
        $this->database->query('TRUNCATE parts');
        $this->database->query('TRUNCATE part_visits');
		$this->database->query('TRUNCATE pages');
		$this->database->query(
			'INSERT INTO pages (ID, url, content) VALUES
			(1, "www.google.com", "<p>google</p>")'
		);
    }
}

(new OwnedPostgresParts)->run();
