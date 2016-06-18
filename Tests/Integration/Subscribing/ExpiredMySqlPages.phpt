<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use Dibi;
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;
use Nette\Security;

require __DIR__ . '/../../bootstrap.php';

final class ExpiredMySqlPages extends TestCase\Database {
	public function testIterating() {
		$this->database->query(
			'INSERT INTO pages (url, content) VALUES
			("www.google.com", "<p>Content google.com</p>"),
			("www.seznam.cz", "<p>Content seznam.cz</p>"),
			("www.facedown.cz", "<p>Content facedown.cz</p>")'
		);
		$this->database->query(
			'INSERT INTO page_visits (page_id, visited_at) VALUES
			(1, "2000-01-01 00:00:00"),
			(2, "2000-01-01 01:00:00"),
			(3, "2000-01-01 00:50:00")'
		);
		$pages = (new Subscribing\ExpiredMySqlPages(
			new Subscribing\FakePages(),
			$this->database,
			new Subscribing\FakeInterval(
				new \DateTimeImmutable('2000-01-01 01:00:00'),
				null,
				new \DateInterval('PT10M') // 10 minutes
			)
		))->iterate();
		Assert::count(2, $pages);
		Assert::same('www.google.com', $pages[0]->url());
		Assert::same('www.facedown.cz', $pages[1]->url());
	}

    protected function prepareDatabase() {
		$this->database->query('TRUNCATE pages');
		$this->database->query('TRUNCATE page_visits');
    }
}

(new ExpiredMySqlPages)->run();
