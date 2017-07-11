<?php
declare(strict_types = 1);
/**
 * @testCase
 */
namespace Remembrall\Postgres;

use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

final class Test extends \Tester\TestCase {
	use TestCase\Database;

	public function testPostgres() {
		foreach (glob(__DIR__ . '/*.sql') as $sql)
			$this->database->exec(file_get_contents($sql));
		$this->database->beginTransaction();
		$test = $this->database->query('SELECT * FROM unit_tests.begin()')->fetch();
		$this->database->rollBack();
		if ($test['result'] !== 'Y')
			echo $test['message'];
		Assert::same('Y', $test['result']);
	}
}

(new Test)->run();