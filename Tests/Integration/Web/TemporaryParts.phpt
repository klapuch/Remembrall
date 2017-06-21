<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Web;

use Klapuch\Dataset;
use Klapuch\Output\FakeFormat;
use Klapuch\Uri;
use Remembrall\Model\Web;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class TemporaryParts extends \Tester\TestCase {
	use TestCase\Redis;

	public function testAddingBrandNewPart() {
		[$expression, $language, $url] = ['//p', 'xpath', new Uri\FakeUri('www.google.com')];
		(new Web\TemporaryParts(
			$this->redis
		))->add(new Web\FakePart(''), $url, $expression, $language);
		Assert::count(1, $this->redis->hgetall('parts'));
	}

	public function testKeepingPartsInReasonableLimit() {
		[$language, $url] = ['xpath', new Uri\FakeUri('www.google.com')];
		$parts = new Web\TemporaryParts($this->redis);
		$parts->add(new Web\FakePart(''), $url, '//h1', $language);
		$parts->add(new Web\FakePart(''), $url, '//h2', $language);
		$parts->add(new Web\FakePart(''), $url, '//h3', $language);
		$parts->add(new Web\FakePart(''), $url, '//h4', $language);
		$parts->add(new Web\FakePart(''), $url, '//h5', $language);
		$parts->add(new Web\FakePart(''), $url, '//h6', $language);
		$parts->add(new Web\FakePart(''), $url, '//h7', $language);
		$pieces = $this->redis->hgetall('parts');
		Assert::count(2, $pieces);
		Assert::contains('//h7', current($pieces));
		next($pieces);
		Assert::contains('//h6', current($pieces));
	}

	public function testStoringEverything() {
		[$expression, $language, $url] = ['//p', 'xpath', new Uri\FakeUri('www.google.com')];
		(new Web\TemporaryParts(
			$this->redis
		))->add(new Web\FakePart('Here I am!'), $url, $expression, $language);
		Assert::equal(
			[
				'content' => 'Here I am!',
				'expression' => '//p',
				'language' => 'xpath',
				'url' => 'www.google.com',
			],
			unserialize(current($this->redis->hgetall('parts')))
		);
	}

	public function testAddingMultipleDifferentParts() {
		[$expression, $language, $url] = ['//p', 'xpath', new Uri\FakeUri('www.google.com')];
		$parts = new Web\TemporaryParts($this->redis);
		$parts->add(new Web\FakePart(''), $url, $expression, $language);
		$parts->add(new Web\FakePart(''), $url, '//h1', $language);
		Assert::count(2, $this->redis->hgetall('parts'));
	}

	public function testAddingSamePartWithoutDuplicity() {
		[$expression, $language, $url] = ['//p', 'xpath', new Uri\FakeUri('www.google.com')];
		$parts = new Web\TemporaryParts($this->redis);
		$parts->add(new Web\FakePart(''), $url, $expression, $language);
		$parts->add(new Web\FakePart(''), $url, $expression, $language);
		Assert::count(1, $this->redis->hgetall('parts'));
	}

	public function testCounting() {
		[$language, $url] = ['xpath', new Uri\FakeUri('www.google.com')];
		$parts = new Web\TemporaryParts($this->redis);
		$parts->add(new Web\FakePart(''), $url, '//p', $language);
		$parts->add(new Web\FakePart(''), $url, '//h1', $language);
		Assert::same(2, $parts->count());
	}

	public function testIteratingAll() {
		[$language, $url] = ['xpath', new Uri\FakeUri('http://www.google.com')];
		$parts = new Web\TemporaryParts($this->redis);
		$parts->add(new Web\FakePart('FOO'), $url, '//p', $language);
		$parts->add(new Web\FakePart('BAR'), $url, '//h1', $language);
		$pieces = iterator_to_array($parts->all(new Dataset\FakeSelection()));
		Assert::count(2, $pieces);
		Assert::contains('BAR', $pieces[0]->print(new FakeFormat())->serialization());
		Assert::contains('FOO', $pieces[1]->print(new FakeFormat())->serialization());
	}
}

(new TemporaryParts)->run();
