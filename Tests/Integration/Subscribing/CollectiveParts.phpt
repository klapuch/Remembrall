<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use GuzzleHttp;
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;
use Klapuch\Uri;

require __DIR__ . '/../../bootstrap.php';

final class CollectiveParts extends TestCase\Database {
	public function testSuccessfulAdding() {
		(new Subscribing\CollectiveParts(
			$this->database
		))->add(
			new Subscribing\FakePart('<p>Content</p>'),
			new Uri\FakeUri('www.google.com'),
			'//p'
		);
		$parts = $this->database->fetchAll(
			'SELECT page_url, content, expression
			FROM parts'
		);
		Assert::count(1, $parts);
		Assert::same('www.google.com', $parts[0]['page_url']);
		Assert::same('<p>Content</p>', $parts[0]['content']);
		Assert::same('//p', $parts[0]['expression']);
		Assert::count(
			1,
			$this->database->fetchAll(
				'SELECT part_id
				FROM part_visits
				WHERE visited_at <= NOW()'
			)
		);
	}

	public function testAddingSamePart() {
		$part = new Subscribing\FakePart('<p>Content</p>');
		Assert::same(
			$part,
			(new Subscribing\CollectiveParts(
				$this->database
			))->add(
				$part,
				new Uri\FakeUri('www.google.com'),
				'//p'
			)
		);
		Assert::same(
			$part,
			(new Subscribing\CollectiveParts(
				$this->database
			))->add(
				$part,
				new Uri\FakeUri('www.google.com'),
				'//p'
			)
		);
        Assert::count(
            1,
            $this->database->fetchAll(
                'SELECT page_url, content, expression
                FROM parts'
            )
        );
	}

	public function testIteratingOverAllPages() {
		$this->database->query(
			"INSERT INTO parts (page_url, expression, content) VALUES
			('www.google.com', '//a', 'a'),
			('www.facedown.cz', '//c', 'c')"
		);
		$this->database->query(
			"INSERT INTO subscriptions (part_id, subscriber_id, interval, last_update) VALUES
			(1, 1, 'PT1M', NOW()),
			(2, 2, 'PT2M', NOW())"
		);
		$parts = (new Subscribing\CollectiveParts(
			$this->database
		))->iterate();
		Assert::count(2, $parts);
		Assert::equal(
			new Subscribing\ConstantPart(
				new Subscribing\HtmlPart(
					new Subscribing\XPathExpression(
						new Subscribing\ConstantPage(
							new Subscribing\FakePage(),
							'<p>google</p>'
						),
						'//a'
					),
					new Subscribing\ConstantPage(
						new Subscribing\FakePage(),
						'<p>google</p>'
					)
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
						new Subscribing\ConstantPage(
							new Subscribing\FakePage(),
							'<p>facedown</p>'
						),
						'//c'
					),
					new Subscribing\ConstantPage(
						new Subscribing\FakePage(),
						'<p>facedown</p>'
					)
				),
				'c',
				'www.facedown.cz'
			),
			$parts[1]
		);
	}

	public function testEmptyParts() {
		Assert::same(
			[],
			(new Subscribing\CollectiveParts(
				$this->database,
				new GuzzleHttp\Client()
			))->iterate()
		);
	}

	protected function prepareDatabase() {
		$this->truncate(['parts', 'part_visits', 'pages', 'subscriptions']);
		$this->restartSequence(['parts', 'part_visits', 'subscriptions']);
		$this->database->query(
			"INSERT INTO pages (url, content) VALUES
			('www.google.com', '<p>google</p>'),
			('www.facedown.cz', '<p>facedown</p>')"
		);
	}
}

(new CollectiveParts)->run();
