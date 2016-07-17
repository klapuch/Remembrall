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

final class OwnedPart extends TestCase\Database {
	public function testContent() {
		Assert::same(
			'd',
			(new Subscribing\OwnedPart(
				new Subscribing\FakePart(),
				$this->database,
				new Subscribing\FakeExpression('//d'),
				new Access\FakeSubscriber(666),
				new Subscribing\FakePage('www.facedown.cz')
			))->content()
		);
	}

	/**
	 * @throws \Remembrall\Exception\NotFoundException You do not own this part
	 */
	public function testContentOnUnknownResult() {
		(new Subscribing\OwnedPart(
			new Subscribing\FakePart(),
			$this->database,
			new Subscribing\FakeExpression('this does not exist'),
			new Access\FakeSubscriber(666),
			new Subscribing\FakePage('this also does not exist')
		))->content();
	}

	public function testDifferentContent() {
		Assert::false(
			(new Subscribing\OwnedPart(
				new Subscribing\FakePart(),
				$this->database,
				new Subscribing\FakeExpression('//d'),
				new Access\FakeSubscriber(666),
				new Subscribing\FakePage('www.facedown.cz')
			))->equals(
				new Subscribing\FakePart('<p>abc</p>')
			)
		);
	}

	public function testEquivalentParts() {
		Assert::true(
			(new Subscribing\OwnedPart(
				new Subscribing\FakePart(),
				$this->database,
				new Subscribing\FakeExpression('//d'),
				new Access\FakeSubscriber(666),
				new Subscribing\FakePage('www.facedown.cz')
			))->equals(
				new Subscribing\FakePart('d')
			)
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
			'INSERT INTO pages (url, content) VALUES
			("www.google.com", "<p>google</p>")'
		);
		$this->database->query(
			'INSERT INTO pages (url, content) VALUES
			("www.facedown.cz", "<p>facedown</p>")'
		);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.facedown.cz", "//b", "b")'
		);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.facedown.cz", "//c", "c")'
		);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.google.com", "//d", "d")'
		);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.facedown.cz", "//d", "d")'
		);
		$this->database->query(
			'INSERT INTO subscribed_parts (part_id, subscriber_id, `interval`) VALUES
			(1, 666, "PT2M"), (2, 666, "PT3M"), (3, 666, "PT4M"), (4, 666, "PT4M")'
		);
	}
}

(new OwnedPart)->run();
