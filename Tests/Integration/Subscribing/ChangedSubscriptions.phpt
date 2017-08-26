<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Subscribing;

use Klapuch\Dataset;
use Klapuch\Storage;
use Nette\Mail;
use Remembrall\Misc;
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ChangedSubscriptions extends \Tester\TestCase {
	use TestCase\Database;

	public function testChangedSnapshotAndPastDateForParticipantAndAuthor() {
		$this->database->exec(
			"INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES 
			(1, 'a', ROW('//a', 'xpath'), 'ac', 'as'),
			(2, 'www.matched.com', ROW('//matched', 'xpath'), 'bc', 'bs'),
			(3, 'c', ROW('//c', 'xpath'), 'cc', 'cs'),
			(4, 'd', ROW('//d', 'xpath'), 'dc', 'ds'),
			(5, 'e', ROW('//e', 'xpath'), 'ec', 'es'),
			(7, 'www.matched2.com', ROW('//matched2', 'xpath'), 'bc2', 'bs2')"
		);
		$this->database->exec(
			"INSERT INTO subscriptions (id, user_id, part_id, interval, last_update, snapshot) VALUES 
			(1, 1, 1, 'PT10S', '2000-01-01', 'as'),
			(2, 2, 2, 'PT10S', '2002-01-01', 'changed'),
			(3, 3, 3, 'PT10S', NOW(), 'changed but time is recent'),
			(4, 4, 4, 'PT10S', NOW(), 'ds'),
			(5, 5, 5, 'PT10S', '2001-01-01', 'es'),
			(6, 2, 7, 'PT20S', '2003-01-01', 'changed2')"
		);
		(new Misc\SampleParticipant($this->database, ['email' => 'me@participant.cz', 'subscription' => 2, 'accepted' => true]))->try();
		(new Misc\SampleParticipant($this->database, ['subscription' => 2, 'accepted' => false]))->try();
		(new Misc\SampleParticipant($this->database, ['subscription' => 2, 'accepted' => false]))->try();
		(new Misc\SampleParticipant($this->database, ['subscription' => 3, 'accepted' => true]))->try();
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
				new Subscribing\StoredSubscription(
					2,
					new Storage\MemoryPDO(
						$this->database,
						[
							'url' => 'www.matched.com',
							'expression' => '//matched',
							'content' => 'bc',
						]
					)
				),
				new Mail\SendmailMailer(),
				['b@b.cz', 'me@participant.cz']
			),
			$subscription
		);
		$subscriptions->next();
		$subscription = $subscriptions->current();
		Assert::equal(
			new Subscribing\EmailSubscription(
				new Subscribing\StoredSubscription(
					6,
					new Storage\MemoryPDO(
						$this->database,
						[
							'url' => 'www.matched2.com',
							'expression' => '//matched2',
							'content' => 'bc2',
						]
					)
				),
				new Mail\SendmailMailer(),
				['b@b.cz']
			),
			$subscription
		);
		$subscriptions->next();
		Assert::null($subscriptions->current());
	}

	public function testFillingTemplateFields() {
		$this->database->exec(
			"INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES 
			(1, 'www.matched.com', ROW('//matched', 'xpath'), 'bc', 'bs')"
		);
		(new Misc\SampleSubscription($this->database, ['user' => 1, 'part' => 1]))->try();
		$this->database->exec(
			"INSERT INTO users (id, email, password, role) VALUES 
			(1, 'a@a.cz', 'a', 'member')"
		);
		$subscriptions = (new Subscribing\ChangedSubscriptions(
			new Subscribing\FakeSubscriptions(),
			new class implements Mail\IMailer {
				public function send(Mail\Message $message) {
					printf(
						'Bcc: %s',
						implode(array_keys($message->getHeader('Bcc')))
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
		Assert::contains('Bcc: a@a.cz', $output);
	}

	public function testPassingWithEmptyIterating() {
		$subscriptions = (new Subscribing\ChangedSubscriptions(
			new Subscribing\FakeSubscriptions(),
			new Mail\SendmailMailer(),
			$this->database
		))->all(new Dataset\FakeSelection(''));
		Assert::null($subscriptions->current());
	}
}

(new ChangedSubscriptions)->run();