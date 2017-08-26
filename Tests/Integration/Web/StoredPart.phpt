<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Web;

use Klapuch\Output;
use Remembrall\Misc;
use Remembrall\Model\Web;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class StoredPart extends \Tester\TestCase {
	use TestCase\Database;

	public function testContent() {
		(new Misc\SamplePart($this->database, ['content' => 'facedown content']))->try();
		(new Misc\SamplePart($this->database))->try();
		Assert::same(
			'facedown content',
			(new Web\StoredPart(
				new Web\FakePart(),
				1,
				$this->database
			))->content()
		);
	}

	public function testSnapshot() {
		(new Misc\SamplePart($this->database, ['snapshot' => 'face snap']))->try();
		(new Misc\SamplePart($this->database))->try();
		Assert::same(
			'face snap',
			(new Web\StoredPart(
				new Web\FakePart(),
				1,
				$this->database
			))->snapshot()
		);
	}

	public function testRefreshingToNewContent() {
		(new Misc\SamplePart($this->database))->try();
		(new Misc\SamplePart($this->database))->try();
		$id = 1;
		(new Web\StoredPart(
			new Web\FakePart('NEW_CONTENT', null, 'NEW_SNAP'),
			$id,
			$this->database
		))->refresh();
		$statement = $this->database->prepare('SELECT * FROM parts WHERE id = ?');
		$statement->execute([$id]);
		$part = $statement->fetch();
		Assert::same('NEW_CONTENT', $part['content']);
		Assert::same('NEW_SNAP', $part['snapshot']);
	}

	public function testRefreshingWithRecordedVisitation() {
		(new Misc\SamplePart($this->database))->try();
		(new Misc\SamplePart($this->database))->try();
		$id = 1;
		$this->truncate(['part_visits']);
		(new Web\StoredPart(
			new Web\FakePart('NEW_CONTENT', null, 'NEW_SNAP'),
			$id,
			$this->database
		))->refresh();
		(new Misc\TableCount($this->database, 'part_visits', 1))->assert();
	}

	public function testRefreshingWithoutAffectingOthers() {
		(new Misc\SamplePart($this->database))->try();
		(new Misc\SamplePart($this->database, ['content' => 'google content', 'snapshot' => 'google snap']))->try();
		$id = 1;
		(new Web\StoredPart(
			new Web\FakePart('NEW_CONTENT', null, 'NEW_SNAP'),
			$id,
			$this->database
		))->refresh();
		(new Misc\TableCount($this->database, 'parts', 2))->assert();
		$parts = $this->database->query('SELECT * FROM parts')->fetchAll();
		Assert::same(2, $parts[0]['id']);
		Assert::same('google content', $parts[0]['content']);
		Assert::same('google snap', $parts[0]['snapshot']);
		Assert::same($id, $parts[1]['id']);
		Assert::same('NEW_CONTENT', $parts[1]['content']);
		Assert::same('NEW_SNAP', $parts[1]['snapshot']);
	}

	public function testAppendedPrintingById() {
		$this->database->exec(
			"INSERT INTO parts (id, page_url, expression, content, snapshot) VALUES
			(1, 'www.facedown.cz', ROW('//facedown', 'css'), 'facedown content', 'face snap'),
			(2, 'www.google.com', ROW('//google', 'xpath'), 'google content', 'google snap')"
		);
		Assert::same(
			'|xxx||id|2||url|www.google.com||expression|//google||content|google content||snapshot|google snap||language|xpath|',
			(new Web\StoredPart(
				new Web\FakePart(),
				2,
				$this->database
			))->print(new Output\FakeFormat('|xxx|'))->serialization()
		);
	}
}

(new StoredPart)->run();