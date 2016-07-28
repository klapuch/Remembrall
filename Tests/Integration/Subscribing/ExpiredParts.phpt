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
        $parts = (new Subscribing\ExpiredParts(
            new Subscribing\FakeParts(),
			$this->database
        ))->iterate();
        Assert::count(2, $parts);
		Assert::equal(
			new Subscribing\ConstantPart(
				new Subscribing\HtmlPart(
					new Subscribing\XPathExpression(
						new Subscribing\ConstantPage('google'),
						'//a'
					),
					new Subscribing\ConstantPage('google')
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
						new Subscribing\ConstantPage('facedown'),
						'//d'
					),
					new Subscribing\ConstantPage('facedown')
				),
				'd',
				'www.facedown.cz'
			),
			$parts[1]
		);
    }

	protected function prepareDatabase() {
		$this->truncate(['part_visits', 'parts', 'pages', 'subscriptions']);
		$this->restartSequence(['part_visits', 'parts', 'subscriptions']);
		$this->database->query(
			'INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, "2016-07-28 20:00"),
			(2, NOW()),
			(3, NOW() - INTERVAL "3 MINUTE"),
			(1, "2016-07-24 20:00")'
		);
		$this->database->query(
			'INSERT INTO parts (page_url, expression, content) VALUES
			("www.google.com", "//a", "a"),
			("www.facedown.cz", "//b", "b"),
			("www.google.com", "//c", "c"),
			("www.facedown.cz", "//d", "d")'
		);
		$this->database->query(
			'INSERT INTO subscriptions (part_id, subscriber_id, interval) VALUES
			(1, 1, "PT10M"),
			(2, 2, "PT10M"),
			(3, 1, "PT10M"),
			(4, 1, "PT10M")'
		);
		$this->database->query(
			'INSERT INTO pages (url, content) VALUES
			("www.google.com", "google"),
			("www.facedown.cz", "facedown"),
			("www.foo.cz", "foo")'
		);
    }
}

(new ExpiredParts)->run();
