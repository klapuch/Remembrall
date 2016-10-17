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

final class CollectiveParts extends TestCase\Database {
	public function testAddingBrandNewPart() {
		(new Subscribing\CollectiveParts(
			$this->database
		))->add(
			new Subscribing\FakePart(
				'<p>google content</p>',
				null,
				'google snap'
			),
			new Uri\FakeUri('www.google.com'),
			'//p'
		);
		$parts = $this->database->fetchAll('SELECT * FROM parts');
		Assert::count(1, $parts);
		Assert::same('www.google.com', $parts[0]['page_url']);
		Assert::same('<p>google content</p>', $parts[0]['content']);
		Assert::same('google snap', $parts[0]['snapshot']);
		Assert::same('//p', $parts[0]['expression']);
	}

	public function testAddingMultipleBrandNewParts() {
		(new Subscribing\CollectiveParts(
			$this->database
		))->add(
			new Subscribing\FakePart(
				'<p>google content</p>',
				null,
				'google snap'
			),
			new Uri\FakeUri('www.google.com'),
			'//google'
		);
		(new Subscribing\CollectiveParts(
			$this->database
		))->add(
			new Subscribing\FakePart(
				'<p>facedown content</p>',
				null,
				'facedown snap'
			),
			new Uri\FakeUri('www.facedown.cz'),
			'//facedown'
		);
		$parts = $this->database->fetchAll('SELECT * FROM parts');
		Assert::count(2, $parts);
		Assert::same('www.google.com', $parts[0]['page_url']);
		Assert::same('<p>google content</p>', $parts[0]['content']);
		Assert::same('google snap', $parts[0]['snapshot']);
		Assert::same('//google', $parts[0]['expression']);
		Assert::same('www.facedown.cz', $parts[1]['page_url']);
		Assert::same('<p>facedown content</p>', $parts[1]['content']);
		Assert::same('facedown snap', $parts[1]['snapshot']);
		Assert::same('//facedown', $parts[1]['expression']);
	}

	public function testAddingPartWithRecordedVisitation() {
		$this->truncate(['part_visits']);
		(new Subscribing\CollectiveParts(
			$this->database
		))->add(
			new Subscribing\FakePart('<p>Content</p>', null, ''),
			new Uri\FakeUri('www.google.com'),
			'//p'
		);
		Assert::count(
			1,
			$this->database->fetchAll('SELECT * FROM part_visits')
		);
	}

	public function testUpdatingPartAsDuplication() {
		$oldPart = new Subscribing\FakePart('<p>Content</p>', null, 'OLD_SNAP');
		(new Subscribing\CollectiveParts(
			$this->database
		))->add($oldPart, new Uri\FakeUri('www.google.com'), '//p');
		$newPart = new Subscribing\FakePart(
			'<p>NEW_CONTENT</p>',
			null,
			'NEW_SNAP'
		);
		(new Subscribing\CollectiveParts(
			$this->database
		))->add($newPart, new Uri\FakeUri('www.google.com'), '//p');
		$parts = $this->database->fetchAll('SELECT * FROM parts');
		Assert::count(1, $parts);
		Assert::same('<p>NEW_CONTENT</p>', $parts[0]['content']);
		Assert::same('NEW_SNAP', $parts[0]['snapshot']);
	}

	public function testUpdatingPartAsDuplicationWithRecordedVisitation() {
		$this->truncate(['part_visits']);
		$part = new Subscribing\FakePart('<p>Content</p>', null, 'snap');
		(new Subscribing\CollectiveParts(
			$this->database
		))->add($part, new Uri\FakeUri('www.google.com'), '//p');
		(new Subscribing\CollectiveParts(
			$this->database
		))->add($part, new Uri\FakeUri('www.google.com'), '//p');
		Assert::count(
			2,
			$this->database->fetchAll('SELECT * FROM part_visits')
		);
	}

	public function testIteratingOverAllPages() {
		$this->database->query(
			"INSERT INTO parts (page_url, expression, content, snapshot) VALUES
			('www.google.com', '//a', 'a', ''),
			('www.facedown.cz', '//c', 'c', '')"
		);
		$parts = (new Subscribing\CollectiveParts(
			$this->database
		))->iterate();
		$part = $parts->current();
		$googleUrl = new Uri\ReachableUrl(new Uri\ValidUrl('www.google.com'));
		$facedownUrl = new Uri\ReachableUrl(
			new Uri\ValidUrl('www.facedown.cz')
		);
		$googlePage = new Subscribing\FrugalPage(
			$googleUrl,
			new Subscribing\PostgresPage(
				new Subscribing\HtmlWebPage(
					new Http\BasicRequest('GET', $googleUrl)
				),
				$googleUrl,
				$this->database
			),
			$this->database
		);
		$facedownPage = new Subscribing\FrugalPage(
			$facedownUrl,
			new Subscribing\PostgresPage(
				new Subscribing\HtmlWebPage(
					new Http\BasicRequest('GET', $facedownUrl)
				),
				$facedownUrl,
				$this->database
			),
			$this->database
		);
		Assert::equal(
			new Subscribing\PostgresPart(
				new Subscribing\HtmlPart(
					new Subscribing\MatchingExpression(
						new Subscribing\XPathExpression($googlePage, '//a')
					),
					$googlePage
				),
				1,
				$this->database
			),
			$part
		);
		$parts->next();
		$part = $parts->current();
		Assert::equal(
			new Subscribing\PostgresPart(
				new Subscribing\HtmlPart(
					new Subscribing\MatchingExpression(
						new Subscribing\XPathExpression($facedownPage, '//c')
					),
					$facedownPage
				),
				2,
				$this->database
			),
			$part
		);
		$parts->next();
		Assert::null($parts->current());
	}

	public function testIteratingWithEmptyParts() {
		$parts = (new Subscribing\CollectiveParts(
			$this->database
		))->iterate();
		Assert::null($parts->current());
	}

	protected function prepareDatabase() {
		$this->purge(['parts']);
	}
}

(new CollectiveParts)->run();
