<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use Klapuch\Output;
use Nette\Mail;
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ChangedSubscriptions extends TestCase\Database {
	public function testIteratingWithDifferentSnapshotAndPastTime() {
		$subscriptions = (new Subscribing\ChangedSubscriptions(
			new Subscribing\FakeSubscriptions(),
			new Mail\SendmailMailer(),
			$this->database
		))->iterate();
		$subscription = $subscriptions->current();
		Assert::equal(
			new Subscribing\EmailSubscription(
				new Subscribing\PostgresSubscription(2, $this->database),
				new Mail\SendmailMailer(),
				(new Mail\Message())
					->setFrom('Remembrall <remembrall@remembrall.org>')
					->addTo('b@b.cz')
					->setSubject(
						'Changes occurred on b page with //b expression'
					)
					->setHtmlBody(
						'<html lang="cs-cz"><body>
<p>
            Hi, there are some changes on
            b
            website with
            //b expression
        </p>
<p>
            Check it out bellow this text
        </p>
<p>bc</p>
</body></html>
'
					)
			),
			$subscription
		);
		$subscriptions->next();
		Assert::null($subscriptions->current());
	}

	public function testEmptyIterating() {
		$this->purge(['parts', 'subscriptions', 'subscribers']);
		$subscriptions = (new Subscribing\ChangedSubscriptions(
			new Subscribing\FakeSubscriptions(),
			new Mail\SendmailMailer(),
			$this->database
		))->iterate();
		Assert::null($subscriptions->current());
	}

	public function testPrinting() {
		$subscriptions = (new Subscribing\ChangedSubscriptions(
			new Subscribing\FakeSubscriptions(),
			new Mail\SendmailMailer(),
			$this->database
		))->print(new Output\FakeFormat());
		Assert::contains('//b', (string)$subscriptions[0]);
	}

	protected function prepareDatabase() {
		$this->purge(['parts', 'subscriptions', 'subscribers', 'part_visits']);
		$this->database->query(
			"INSERT INTO parts (page_url, expression, content, snapshot) VALUES 
			('a', '//a', 'ac', 'as'),
			('b', '//b', 'bc', 'bs'),
			('c', '//c', 'cc', 'cs'),
			('d', '//d', 'dc', 'ds'),
			('e', '//e', 'ec', 'es')"
		);
		$this->database->query(
			"INSERT INTO subscriptions (subscriber_id, part_id, interval, last_update, snapshot) VALUES 
			(1, 1, 'PT10S', '2000-01-01', 'as'),
			(2, 2, 'PT10S', '2002-01-01', 'changed'),
			(3, 3, 'PT10S', NOW(), 'changed but time is recent'),
			(4, 4, 'PT10S', NOW(), 'ds'),
			(5, 5, 'PT10S', '2001-01-01', 'es')"
		);
		$this->database->query(
			"INSERT INTO subscribers (id, email, password) VALUES 
			(1, 'a@a.cz', 'a'),
			(2, 'b@b.cz', 'b'),
			(3, 'c@c.cz', 'c'),
			(4, 'd@d.cz', 'd'),
			(5, 'e@e.cz', 'e')"
		);
	}
}

(new ChangedSubscriptions)->run();
