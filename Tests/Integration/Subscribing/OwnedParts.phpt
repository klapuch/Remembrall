<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use Remembrall\Model\{
	Subscribing, Access, Http
};
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class OwnedParts extends TestCase\Database {
	public function testIteratingOwnedParts() {
		$this->database->query(
			'INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, NOW() - INTERVAL "10 MINUTE"),
			(2, NOW() - INTERVAL "20 MINUTE"),
			(3, NOW() - INTERVAL "30 MINUTE"),
			(4, NOW() - INTERVAL "40 MINUTE"),
			(1, NOW() - INTERVAL "50 MINUTE")'
		);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.google.com", "//a", "a"),
			("www.facedown.cz", "//b", "b"),
			("www.facedown.cz", "//c", "c"),
			("www.google.com", "//d", "d")'
		);
		$this->database->query(
			'INSERT INTO subscriptions (part_id, subscriber_id, interval) VALUES
			(1, 1, "PT1M"),
			(2, 2, "PT2M"),
			(3, 1, "PT3M"),
			(4, 1, "PT4M")'
		);
		$parts = (new Subscribing\OwnedParts(
			new Subscribing\FakeParts(),
			$this->database,
			new Access\FakeSubscriber(1)
		))->iterate();
		Assert::count(3, $parts);
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
		Assert::equal(
			new Subscribing\ConstantPart(
				new Subscribing\HtmlPart(
					new Subscribing\XPathExpression(
						new Subscribing\ConstantPage(
							new Subscribing\FakePage(),
							'<p>google</p>'
						),
						'//d'
					),
					new Subscribing\ConstantPage(
						new Subscribing\FakePage(),
						'<p>google</p>'
					)
				),
				'd',
				'www.google.com'
			),
			$parts[2]
		);
	}

	public function testEmptyParts() {
		Assert::same(
			[],
			(new Subscribing\OwnedParts(
				new Subscribing\FakeParts(),
				$this->database,
				new Access\FakeSubscriber(777)
			))->iterate()
		);
	}

	protected function prepareDatabase() {
		$this->truncate(['parts', 'part_visits', 'pages', 'subscriptions']);
		$this->restartSequence(['parts', 'part_visits', 'subscriptions']);
		$this->database->query(
			'INSERT INTO pages (url, content) VALUES
			("www.google.com", "<p>google</p>"),
			("www.facedown.cz", "<p>facedown</p>")'
		);
	}
}

(new OwnedParts)->run();
