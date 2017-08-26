<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Web;

use Klapuch\Uri;
use Remembrall\Misc;
use Remembrall\Model\Web;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class FrugalPage extends \Tester\TestCase {
	use TestCase\Database;

	public function testFrugalPage() {
		(new Misc\SamplePage($this->database, ['url' => 'www.google.com', 'content' => 'google']))->try();
		Assert::contains(
			'google',
			(new Web\FrugalPage(
				new Uri\FakeUri('www.google.com'),
				new Web\FakePage(),
				$this->database
			))->content()->saveHTML()
		);
	}

	public function testFrugalPageWithMultipleVisitation() {
		$this->database->exec(
			"INSERT INTO page_visits (page_url, visited_at) VALUES
			('www.google.com', NOW() - INTERVAL '70 MINUTE'),
			('www.google.com', NOW() - INTERVAL '20 MINUTE')"
		);
		(new Misc\SamplePage($this->database, ['url' => 'www.google.com', 'content' => 'google']))->try();
		Assert::contains(
			'google',
			(new Web\FrugalPage(
				new Uri\FakeUri('www.google.com'),
				new Web\FakePage(),
				$this->database
			))->content()->saveHTML()
		);
	}

	public function testOutdatedPage() {
		(new Misc\SamplePage($this->database, ['url' => 'www.google.com']))->try();
		$this->truncate(['page_visits']);
		$this->database->exec(
			"INSERT INTO page_visits (page_url, visited_at) VALUES
			('www.google.com', NOW() - INTERVAL '11 MINUTE')"
		);
		$dom = new \DOMDocument();
		$dom->loadHTML('<p>Google</p>');
		Assert::contains(
			'<p>Google</p>',
			(new Web\FrugalPage(
				new Uri\FakeUri('www.google.com'),
				new Web\FakePage(
					new \DOMDocument(),
					new Web\FakePage($dom)
				),
				$this->database
			))->content()->saveHTML()
		);
	}

	public function testOutdatedPageWithMultipleVisitation() {
		(new Misc\SamplePage($this->database, ['url' => 'www.google.com']))->try();
		$this->truncate(['page_visits']);
		$this->database->exec(
			"INSERT INTO page_visits (page_url, visited_at) VALUES
			('www.google.com', NOW() - INTERVAL '11 MINUTE'),
			('www.google.com', NOW() - INTERVAL '20 MINUTE'),
			('www.google.com', NOW() - INTERVAL '70 MINUTE')"
		);
		$dom = new \DOMDocument();
		$dom->loadHTML('<p>Google</p>');
		Assert::contains(
			'<p>Google</p>',
			(new Web\FrugalPage(
				new Uri\FakeUri('www.google.com'),
				new Web\FakePage(
					new \DOMDocument(),
					new Web\FakePage($dom)
				),
				$this->database
			))->content()->saveHTML()
		);
	}

	public function testOriginContentAsFirstVisit() {
		$dom = new \DOMDocument();
		$dom->loadHTML('<p>Google</p>');
		Assert::contains(
			'<p>Google</p>',
			(new Web\FrugalPage(
				new Uri\FakeUri('www.google.com'),
				new Web\FakePage(
					$dom,
					new Web\FakePage(new \DOMDocument())
				),
				$this->database
			))->content()->saveHTML()
		);
	}
}

(new FrugalPage())->run();