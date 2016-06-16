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

final class ExpiredPostgresParts extends TestCase\Database {
    public function testIteratingExpiredPartsOnConcretePage() {
        $this->database->query(
            'INSERT INTO parts (url, expression, content, visited_at, `interval`, subscriber_id) VALUES
			("a", "//a", "a", NOW() - INTERVAL 10 MINUTE, 10, 1)'
        );
        $this->database->query(
            'INSERT INTO parts (url, expression, content, visited_at, `interval`, subscriber_id) VALUES
			("b", "//b", "b", NOW(), 2, 2)'
        );
        $this->database->query(
            'INSERT INTO parts (url, expression, content, visited_at, `interval`, subscriber_id) VALUES
			("c", "//c", "c", NOW(), 3, 1)'
        );
        $this->database->query(
            'INSERT INTO parts (url, expression, content, visited_at, `interval`, subscriber_id) VALUES
			("a", "//d", "d", NOW() - INTERVAL 2 DAY, 10, 1)'
        );
        $parts = (new Subscribing\OwnedPostgresParts(
            $this->database,
            new Subscribing\FakePage('a'),
            new Security\Identity(1)
        ))->iterate();
        Assert::same(2, count($parts));
        Assert::same('//a', (string)$parts[0]->expression());
        Assert::same('//d', (string)$parts[1]->expression());
    }

    protected function prepareDatabase() {
        $this->database->query('TRUNCATE parts');
    }
}

(new ExpiredPostgresParts)->run();
