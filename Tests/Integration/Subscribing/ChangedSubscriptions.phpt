<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Subscribing;

use Klapuch\Dataset;
use Nette\Mail;
use Remembrall\Model\Subscribing;
use Remembrall\Model\Web;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ChangedSubscriptions extends TestCase\Database {
	public function testChangedSnapshotAndPastDateForParticipantAndAuthor() {
		$this->database->exec(
			"INSERT INTO parts (page_url, expression, content, snapshot) VALUES 
			('a', '//a', 'ac', 'as'),
			('www.matched.com', '//matched', 'bc', 'bs'),
			('c', '//c', 'cc', 'cs'),
			('d', '//d', 'dc', 'ds'),
			('e', '//e', 'ec', 'es')"
		);
		$this->database->exec(
			"INSERT INTO subscriptions (user_id, part_id, interval, last_update, snapshot) VALUES 
			(1, 1, 'PT10S', '2000-01-01', 'as'),
			(2, 2, 'PT10S', '2002-01-01', 'changed'),
			(3, 3, 'PT10S', NOW(), 'changed but time is recent'),
			(4, 4, 'PT10S', NOW(), 'ds'),
			(5, 5, 'PT10S', '2001-01-01', 'es')"
		);
		$this->database->exec(
			"INSERT INTO participants (email, subscription_id, code, invited_at, accepted, decided_at) VALUES
			('me@participant.cz', 2, 'abc', NOW(), TRUE, NOW()),
			('foo@participant.cz', 2, 'abc', NOW(), FALSE, NOW()),
			('bar@participant.cz', 2, 'abc', NOW(), FALSE, NULL),
			('baz@participant.cz', 3, 'abc', NOW(), TRUE, NOW())"
		);
		$this->database->exec(
			"INSERT INTO users (id, email, password, role) VALUES 
			(1, 'a@a.cz', 'a', 'member'),
			(2, 'b@b.cz', 'b', 'member'),
			(3, 'c@c.cz', 'c', 'member'),
			(4, 'd@d.cz', 'd', 'member'),
			(5, 'e@e.cz', 'e', 'member')"
		);
		$subscriptions = (new Subscribing\ChangedSubscriptions(
			new Subscribing\FakeSubscriptions(),
			new Mail\SendmailMailer(),
			$this->database
		))->all(new Dataset\FakeSelection(''));
		$subscription = $subscriptions->current();
		Assert::equal(
			new Subscribing\EmailSubscription(
				new Subscribing\StoredSubscription(2, $this->database),
				new Mail\SendmailMailer(),
				'b@b.cz',
				new Web\ConstantPart(
					new Web\FakePart(),
					'bc',
					'bs',
					[
						'url' => 'www.matched.com',
						'expression' => '//matched',
						'content' => 'bc',
					]
				)
			),
			$subscription
		);
		$subscriptions->next();
		$subscription = $subscriptions->current();
		Assert::equal(
			new Subscribing\EmailSubscription(
				new Subscribing\StoredSubscription(2, $this->database),
				new Mail\SendmailMailer(),
				'me@participant.cz',
				new Web\ConstantPart(
					new Web\FakePart(),
					'bc',
					'bs',
					[
						'url' => 'www.matched.com',
						'expression' => '//matched',
						'content' => 'bc',
					]
				)
			),
			$subscription
		);
		$subscriptions->next();
		Assert::null($subscriptions->current());
	}

	public function testTemplateFields() {
		$this->database->exec(
			"INSERT INTO parts (page_url, expression, content, snapshot) VALUES 
			('www.matched.com', '//matched', 'bc', 'bs')"
		);
		$this->database->exec(
			"INSERT INTO subscriptions (user_id, part_id, interval, last_update, snapshot) VALUES 
			(1, 1, 'PT10S', '2000-01-01', 'as')"
		);
		$this->database->exec(
			"INSERT INTO users (id, email, password, role) VALUES 
			(1, 'a@a.cz', 'a', 'member')"
		);
		$subscriptions = (new Subscribing\ChangedSubscriptions(
			new Subscribing\FakeSubscriptions(),
			new class implements Mail\IMailer {
				public function send(Mail\Message $message) {
					printf(
						'To: %s',
						implode(array_keys($message->getHeader('To')))
					);
					printf('Subject: %s', $message->getSubject());
					printf('Body: %s', $message->getHtmlBody());
				}
			},
			$this->database
		))->all(new Dataset\FakeSelection(''));
		$subscription = $subscriptions->current();
		ob_start();
		$subscription->notify();
		$output = ob_get_clean();
		Assert::contains('www.matched.com', $output);
		Assert::contains('//matched', $output);
		Assert::contains('bc', $output);
	}

	public function testEmptyIterating() {
		$subscriptions = (new Subscribing\ChangedSubscriptions(
			new Subscribing\FakeSubscriptions(),
			new Mail\SendmailMailer(),
			$this->database
		))->all(new Dataset\FakeSelection(''));
		Assert::null($subscriptions->current());
	}

	protected function prepareDatabase(): void {
		$this->purge(['parts', 'subscriptions', 'users', 'part_visits', 'participants', 'invitation_attempts']);
	}
}

(new ChangedSubscriptions)->run();