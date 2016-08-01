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

require __DIR__ . '/../../bootstrap.php';

final class ExpiredParts extends TestCase\Database {
	public function testIteratingExpiredParts() {
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.google.com", "//a", "a"),
			("www.facedown.cz", "//b", "b"),
			("www.google.com", "//c", "c"),
			("www.facedown.cz", "//d", "d")'
		);
		$this->database->query(
			'INSERT INTO subscriptions (part_id, subscriber_id, interval, last_update) VALUES
			(1, 1, "PT10M", NOW() - INTERVAL "15 MINUTE"),
			(2, 2, "PT10M", NOW()),
			(3, 3, "PT3M", NOW() - INTERVAL "2 MINUTE"),
			(3, 1, "PT20M", NOW() - INTERVAL "22 MINUTE")'
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
		Assert::count(2, $parts);
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
			$parts[1]
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
		$this->truncate(['parts', 'part_visits', 'pages', 'subscriptions']);
		$this->restartSequence(['parts', 'subscriptions']);
	}
}

(new ExpiredParts)->run();
