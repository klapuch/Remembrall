<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;
use Klapuch\{
	Uri, Http
};

require __DIR__ . '/../../bootstrap.php';

final class OutdatedParts extends TestCase\Database {
	public function testIteratingOutdatedParts() {
		$this->database->query(
			"INSERT INTO parts (page_url, expression, content) VALUES
			('www.google.com', '//a', 'a'),
			('www.facedown.cz', '//b', 'b'),
			('www.google.com', '//c', 'c'),
			('www.facedown.cz', '//d', 'd')"
		);
		$this->database->query(
			"INSERT INTO subscriptions (part_id, subscriber_id, interval, last_update) VALUES
			(1, 1, 'PT10M', NOW() - INTERVAL '15 MINUTE'),
			(2, 2, 'PT10M', NOW()),
			(3, 3, 'PT3M', NOW() - INTERVAL '2 MINUTE'),
			(3, 1, 'PT20M', NOW() - INTERVAL '22 MINUTE')"
		);
		$parts = (new Subscribing\OutdatedParts(
			new Subscribing\FakeParts(),
			$this->database
		))->iterate();
		$part = $parts->current();
		$url = new Uri\ReachableUrl(new Uri\ValidUrl('www.google.com'));
		$page = new Subscribing\CachedPage(
			$url,
			new Subscribing\PostgresPage(
				new Subscribing\HtmlWebPage(
					new Http\BasicRequest('GET', $url)),
				$url,
				$this->database
			),
			$this->database
		);
		Assert::equal(
			new Subscribing\PostgresPart(
				new Subscribing\HtmlPart(
					new Subscribing\MatchingExpression(
						new Subscribing\XPathExpression(
							$page,
							'//c'
						)
					),
					$page
				),
				3,
				$this->database
			),
			$part
		);
		$parts->next();
		$part = $parts->current();
		$url = new Uri\ReachableUrl(new Uri\ValidUrl('www.google.com'));
		$page = new Subscribing\CachedPage(
			$url,
			new Subscribing\PostgresPage(
				new Subscribing\HtmlWebPage(
					new Http\BasicRequest('GET', $url)),
				$url,
				$this->database
			),
			$this->database
		);
		Assert::equal(
			new Subscribing\PostgresPart(
				new Subscribing\HtmlPart(
					new Subscribing\MatchingExpression(
						new Subscribing\XPathExpression(
							$page,
							'//a'
						)
					),
					$page
				),
				1,
				$this->database
			),
			$part
		);
		$parts->next();
		Assert::null($parts->current());
	}

	public function testEmptyIterating() {
		$parts = (new Subscribing\OutdatedParts(
			new Subscribing\FakeParts(),
			$this->database
		))->iterate();
		Assert::null($parts->current());
	}

	protected function prepareDatabase() {
		$this->truncate(['parts', 'part_visits', 'subscriptions']);
		$this->restartSequence(['parts', 'subscriptions']);
	}
}

(new OutdatedParts)->run();
