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
		Assert::count(1, $this->redis->hgetall('parts_access'));
	}

	public function testAddingWithRemovingAllExpired() {
		[$expression, $language, $url] = ['//p', 'xpath', new Uri\FakeUri('www.google.com')];
		$this->redis->hset('parts', 'abc', 'data');
		$this->redis->hset('parts_access', 'abc', strtotime('-1 hour'));
		$this->redis->hset('parts', 'def', 'data');
		$this->redis->hset('parts_access', 'def', strtotime('-40 minutes'));
		(new Web\TemporaryParts(
			$this->redis
		))->add(new Web\FakePart(''), $url, $expression, $language);
		Assert::count(1, $this->redis->hgetall('parts'));
		Assert::count(1, $this->redis->hgetall('parts_access'));
	}

	public function testKeepingNonExpired() {
		[$expression, $language, $url] = ['//p', 'xpath', new Uri\FakeUri('www.google.com')];
		$this->redis->hset('parts', 'abc', 'data');
		$this->redis->hset('parts_access', 'abc', strtotime('-10 minutes'));
		$this->redis->hset('parts', 'def', 'data');
		$this->redis->hset('parts_access', 'def', strtotime('-20 minutes'));
		(new Web\TemporaryParts(
			$this->redis
		))->add(new Web\FakePart(''), $url, $expression, $language);
		Assert::count(3, $this->redis->hgetall('parts'));
		Assert::count(3, $this->redis->hgetall('parts_access'));
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
		Assert::count(2, $this->redis->hgetall('parts_access'));
	}

	public function testAddingSamePartWithoutDuplicity() {
		[$expression, $language, $url] = ['//p', 'xpath', new Uri\FakeUri('www.google.com')];
		$parts = new Web\TemporaryParts($this->redis);
		$parts->add(new Web\FakePart(''), $url, $expression, $language);
		$parts->add(new Web\FakePart(''), $url, $expression, $language);
		Assert::count(1, $this->redis->hgetall('parts'));
		Assert::count(1, $this->redis->hgetall('parts_access'));
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
		Assert::contains('http://www.google.com', $pieces[0]->print(new FakeFormat())->serialization());
		Assert::contains('http://www.google.com', $pieces[1]->print(new FakeFormat())->serialization());
	}
}

(new TemporaryParts)->run();
