<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use Dibi;
use Remembrall\Model\{
	Subscribing, Access, Http
};
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class CollectiveParts extends TestCase\Database {
    public function testAdding() {
		$this->database->query(
			'INSERT INTO subscribers (id, email, password) VALUES
			(1, "foo@bar.cz", "secret"), (2, "facedown@facedown.cz", "secret")'
		);
        (new Subscribing\CollectiveParts(
            $this->database
		))->add(
			new Subscribing\FakePart('<p>Content</p>'),
			'www.google.com',
			'//p'
		);
		$parts = $this->database->fetchAll(
			'SELECT id, page_url, content, expression FROM parts'
		);
		Assert::count(1, $parts);
		Assert::same(1, $parts[0]['id']);
		Assert::same('www.google.com', $parts[0]['page_url']);
		Assert::same('<p>Content</p>', $parts[0]['content']);
		Assert::same('//p', $parts[0]['expression']);
		$partVisits = $this->database->fetchAll(
			'SELECT part_id FROM part_visits WHERE visited_at <= ?',
			new \DateTimeImmutable()
		);
		Assert::count(1, $partVisits);
    }

	public function testTwiceAddingWithUpdate() {
		$this->database->query(
			'INSERT INTO subscribers (id, email, password) VALUES
			(1, "foo@bar.cz", "secret"), (2, "facedown@facedown.cz", "secret")'
		);
		$refreshedPart = new Subscribing\FakePart('<p>Updated content</p>');
		$part = new Subscribing\FakePart('<p>Content</p>', null, $refreshedPart);
		Assert::same(
			$part,
			(new Subscribing\CollectiveParts(
				$this->database
			))->add(
				$part,
				'www.google.com',
				'//p'
			)
		);
		Assert::same(
			$refreshedPart,
			(new Subscribing\CollectiveParts(
				$this->database
			))->add(
				$part,
				'www.google.com',
				'//p'
			)
		);
		Assert::count(
			2,
			$this->database->fetchAll(
				'SELECT part_id FROM part_visits'
			)
		);
	}

	public function testIteratingOverAllPages() {
		$this->database->query(
			'INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, NOW()), (2, NOW()), (2, NOW() - INTERVAL "5 MINUTE")'
		);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.google.com", "//a", "a")'
		);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.facedown.cz", "//c", "c")'
		);
		$this->database->query(
			'INSERT INTO subscriptions (part_id, subscriber_id, interval) VALUES
			(1, 1, "PT1M"), (2, 2, "PT2M")'
		);
		$parts = (new Subscribing\CollectiveParts(
			$this->database
		))->iterate();
		Assert::count(2, $parts);
		Assert::equal(
			new Subscribing\ConstantPart(
				new Subscribing\HtmlPart(
					new Subscribing\XPathExpression(
						new Subscribing\ConstantPage('<p>google</p>'),
						'//a'
					),
					new Subscribing\ConstantPage('<p>google</p>')
				),
				'a',
				'www.google.com'
			),
			$parts[0]
		);
		Assert::equal(
			new Subscribing\ConstantPart(
				new Subscribing\HtmlPart(
					new Subscribing\XPathExpression(
						new Subscribing\ConstantPage('<p>facedown</p>'),
						'//c'
					),
					new Subscribing\ConstantPage('<p>facedown</p>')
				),
				'c',
				'www.facedown.cz'
			),
			$parts[1]
		);
	}

    protected function prepareDatabase() {
		$this->truncate(['parts', 'part_visits', 'pages', 'subscribers', 'subscriptions']);
		$this->restartSequence(['parts', 'part_visits', 'subscribers', 'subscriptions']);
		$this->database->query(
			'INSERT INTO pages (url, content) VALUES
			("www.google.com", "<p>google</p>")'
		);
		$this->database->query(
			'INSERT INTO pages (url, content) VALUES
			("www.facedown.cz", "<p>facedown</p>")'
		);
    }
}

(new CollectiveParts)->run();
