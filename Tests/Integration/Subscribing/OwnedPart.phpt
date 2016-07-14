<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use Dibi;
use Remembrall\Model\{
	Access, Subscribing
};
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class OwnedPart extends TestCase\Database {
	public function testSource() {
		$source = new Subscribing\FakePage();
		Assert::same(
			$source,
			(new Subscribing\OwnedPart(
				$this->database,
				new Subscribing\FakeExpression(),
				new Access\FakeSubscriber(),
				$source
			))->source()
		);
	}

	public function testExpression() {
		$expression = new Subscribing\FakeExpression();
		Assert::same(
			$expression,
			(new Subscribing\OwnedPart(
				$this->database,
				$expression,
				new Access\FakeSubscriber(),
				new Subscribing\FakePage()
			))->expression()
		);
	}

	public function testSameContentButDifferentPage() {
		Assert::false(
			(new Subscribing\OwnedPart(
				$this->database,
				new Subscribing\FakeExpression,
				new Access\FakeSubscriber(),
				new Subscribing\FakePage('google.com')
			))->equals(
				new Subscribing\FakePart(
					new Subscribing\FakePage('seznam.cz')
				)
			)
		);
	}

	public function testDifferentContentButSamePage() {
		Assert::false(
			(new Subscribing\OwnedPart(
				$this->database,
				new Subscribing\FakeExpression('//b'),
				new Access\FakeSubscriber(666),
				new Subscribing\FakePage('www.facedown.cz')
			))->equals(
				new Subscribing\FakePart(
					new Subscribing\FakePage('www.facedown.cz'),
					null,
					''
				)
			)
		);
	}

	public function testEquivalentParts() {
		Assert::true(
			(new Subscribing\OwnedPart(
				$this->database,
				new Subscribing\FakeExpression('//b'),
				new Access\FakeSubscriber(666),
				new Subscribing\FakePage('www.facedown.cz')
			))->equals(
				new Subscribing\FakePart(
					new Subscribing\FakePage('www.facedown.cz'),
					null,
					'b'
				)
			)
		);
	}

	public function testContent() {
		Assert::same(
			'd',
			(new Subscribing\OwnedPart(
				$this->database,
				new Subscribing\FakeExpression('//d'),
				new Access\FakeSubscriber(666),
				new Subscribing\FakePage('www.facedown.cz')
			))->content()
		);
	}

	public function testContentOnUnknownResult() {
		Assert::same(
			'',
			(new Subscribing\OwnedPart(
				$this->database,
				new Subscribing\FakeExpression('this does not exist'),
				new Access\FakeSubscriber(666),
				new Subscribing\FakePage('this also does not exist')
			))->content()
		);
	}

	public function testVisitation() {
		Assert::equal(
			new Subscribing\DateTimeInterval(
				new \DateTimeImmutable('2000-01-01 01:01:01'),
				new \DateInterval('PT3M')
			),
			(new Subscribing\OwnedPart(
				$this->database,
				new Subscribing\FakeExpression('//c'),
				new Access\FakeSubscriber(666),
				new Subscribing\FakePage('www.facedown.cz')
			))->visitedAt()
		);
	}

    protected function prepareDatabase() {
		$this->database->query('TRUNCATE parts');
		$this->database->query('TRUNCATE part_visits');
		$this->database->query('TRUNCATE pages');
		$this->database->query('TRUNCATE subscribed_parts');
		$this->database->query(
			'INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, NOW()), (2, "2000-01-01 01:01:01"), (3, NOW()), (4, NOW())'
		);
		$this->database->query(
			'INSERT INTO pages (ID, url, content) VALUES
			(1, "www.google.com", "<p>google</p>")'
		);
		$this->database->query(
			'INSERT INTO pages (ID, url, content) VALUES
			(2, "www.facedown.cz", "<p>facedown</p>")'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content) VALUES
			(2, "//b", "b")'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content) VALUES
			(2, "//c", "c")'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content) VALUES
			(1, "//d", "d")'
		);
		$this->database->query(
			'INSERT INTO parts (page_id, expression, content) VALUES
			(2, "//d", "d")'
		);
		$this->database->query(
			'INSERT INTO subscribed_parts (part_id, subscriber_id, `interval`) VALUES
			(1, 666, "PT2M"), (2, 666, "PT3M"), (3, 666, "PT4M"), (4, 666, "PT4M")'
		);
    }
}

(new OwnedPart)->run();
