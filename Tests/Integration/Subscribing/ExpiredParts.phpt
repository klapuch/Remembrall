<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use Remembrall\Model\{
	Http, Subscribing
};
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ExpiredParts extends TestCase\Database {
	public function testIteratingExpiredParts() {
		$this->database->query(
			'INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, NOW() - INTERVAL "2 DAY"),
			(2, NOW()),
			(3, NOW() - INTERVAL "10 MINUTE"),
			(1, NOW() - INTERVAL "4 DAY")'
		);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content, content_hash) VALUES
			("www.google.com", "//a", "a", MD5("a")),
			("www.facedown.cz", "//b", "b", MD5("b")),
			("www.google.com", "//c", "c", MD5("c")),
			("www.facedown.cz", "//d", "d", MD5("d"))'
		);
		$this->database->query(
			'INSERT INTO subscriptions (part_id, subscriber_id, interval) VALUES
			(1, 1, "PT10M"),
			(2, 2, "PT10M"),
			(3, 3, "PT3M"),
			(3, 1, "PT20M"),
			(4, 1, "PT10M")'
		);
		$this->database->query(
			'INSERT INTO pages (url, content) VALUES
			("www.google.com", "google"),
			("www.facedown.cz", "facedown"),
			("www.foo.cz", "foo")'
		);
		$parts = (new Subscribing\ExpiredParts(
			new Subscribing\FakeParts(),
			$this->database
		))->iterate();
		Assert::count(3, $parts);
		Assert::equal(
			new Subscribing\ConstantPart(
				new Subscribing\HtmlPart(
					new Subscribing\XPathExpression(
						new Subscribing\ConstantPage(
							new Subscribing\FakePage(),
							'google'
						),
						'//a'
					),
					new Subscribing\ConstantPage(
						new Subscribing\FakePage(),
						'google'
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
							'google'
						),
						'//c'
					),
					new Subscribing\ConstantPage(
						new Subscribing\FakePage(),
						'google'
					)
				),
				'c',
				'www.google.com'
			),
			$parts[1]
		);
		Assert::equal(
			new Subscribing\ConstantPart(
				new Subscribing\HtmlPart(
					new Subscribing\XPathExpression(
						new Subscribing\ConstantPage(
							new Subscribing\FakePage(),
							'facedown'
						),
						'//d'
					),
					new Subscribing\ConstantPage(
						new Subscribing\FakePage(),
						'facedown'
					)
				),
				'd',
				'www.facedown.cz'
			),
			$parts[2]
		);
	}

	public function testEmptyParts() {
		Assert::same(
			[],
			(new Subscribing\ExpiredParts(
				new Subscribing\FakeParts(),
				$this->database
			))->iterate()
		);
	}

	protected function prepareDatabase() {
		$this->truncate(['part_visits', 'parts', 'pages', 'subscriptions']);
		$this->restartSequence(['part_visits', 'parts', 'subscriptions']);
	}
}

(new ExpiredParts)->run();
