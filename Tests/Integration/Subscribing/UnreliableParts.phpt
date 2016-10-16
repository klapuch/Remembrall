<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use Klapuch\{
	Http, Uri
};
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class UnreliableParts extends TestCase\Database {
	public function testIteratingUnreliableParts() {
		$this->database->query(
			"INSERT INTO parts (page_url, expression, content, snapshot) VALUES
			('www.google.com', '//a', 'a', ''),
			('www.facedown.cz', '//b', 'b', ''),
			('www.google.com', '//c', 'c', ''),
			('www.facedown.cz', '//d', 'd', ''),
			('www.new.cz', '//e', 'e', '')"
		);
		$this->truncate(['part_visits']);
		$this->database->query(
			"INSERT INTO part_visits (part_id, visited_at) VALUES
			(1, NOW() - INTERVAL '50 SECOND'),
			(1, NOW() - INTERVAL '10 SECOND'),
			(1, NOW() - INTERVAL '20 SECOND'),
			(2, NOW()),
			(2, NOW() - INTERVAL '5 SECOND'),
			(4, NOW() - INTERVAL '45 SECOND')"
		);
		$this->database->query(
			"INSERT INTO subscriptions (part_id, subscriber_id, interval, last_update, snapshot) VALUES
			(1, 1, 'PT10S', NOW(), ''),
			(1, 2, 'PT50S', NOW(), ''),
			(1, 3, 'PT5S', NOW(), ''),
			(2, 3, 'PT10S', NOW(), ''),
			(2, 4, 'PT50S', NOW(), ''),
			(4, 1, 'PT10S', NOW(), '')"
		);
		$parts = (new Subscribing\UnreliableParts(
			new Subscribing\FakeParts(),
			$this->database
		))->iterate();
		$part = $parts->current();
		$url = new Uri\ReachableUrl(new Uri\ValidUrl('www.facedown.cz'));
		$page = new Subscribing\CachedPage(
			$url,
			new Subscribing\PostgresPage(
				new Subscribing\HtmlWebPage(
					new Http\BasicRequest('GET', $url)
				),
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
							'//d'
						)
					),
					$page
				),
				4,
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
					new Http\BasicRequest('GET', $url)
				),
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
		$parts = (new Subscribing\UnreliableParts(
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

(new UnreliableParts)->run();
