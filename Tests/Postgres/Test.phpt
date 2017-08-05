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
		$output = (new class (new \SplFileInfo(__DIR__), $this->database) {
			private const PATTERN = '~\.sql$~i';
			private $source;
			private $database;

			public function __construct(\SplFileInfo $source, \PDO $database) {
				$this->source = $source;
				$this->database = $database;
			}

			public function result(): array {
				$this->import($this->tests($this->source), $this->database);
				$this->database->beginTransaction();
				try {
					return $this->database->query('SELECT * FROM unit_tests.begin()')->fetch();
				} finally {
					$this->database->rollBack();
				}
			}

			private function import(\Iterator $tests, \PDO $database): void {
				foreach ($tests as $test)
					$database->exec(file_get_contents($test->getPathname()));
			}

			private function tests(\SplFileInfo $source): \Iterator {
				return new \RegexIterator(
					new \DirectoryIterator($source->getPathname()),
					self::PATTERN
				);
			}
		})->result();
		try {
			Assert::same('Y', $output['result']);
		} finally {
			echo $output['message'];
		}
	}
}
(new Test)->run();