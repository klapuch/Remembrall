<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Web;

use Klapuch\Output;
use Klapuch\Uri;
use Remembrall\Model\Web;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ThrowawayPart extends \Tester\TestCase {
	use TestCase\Redis;

	public function testPrintingExisting() {
		[$expression, $language, $url] = ['//p', 'xpath', new Uri\FakeUri('www.google.com')];
		(new Web\TemporaryParts(
			$this->redis
		))->add(new Web\FakePart('CONTENT'), $url, $expression, $language);
		Assert::same(
			'|content|CONTENT||url|www.google.com||expression|//p||language|xpath|',
			(new Web\ThrowawayPart(
				$this->redis,
				$url,
				$expression,
				$language
			))->print(new Output\FakeFormat(''))->serialization()
		);
	}

	public function testRemovingAfterPrinting() {
		[$expression, $language, $url] = ['//p', 'xpath', new Uri\FakeUri('www.google.com')];
		(new Web\TemporaryParts(
			$this->redis
		))->add(new Web\FakePart('CONTENT'), $url, $expression, $language);
		(new Web\ThrowawayPart(
			$this->redis,
			$url,
			$expression,
			$language
		))->print(new Output\FakeFormat(''));
		Assert::count(0, $this->redis->hgetall('parts'));
	}

	public function testThrowingOnPrintingUnknown() {
		$ex = Assert::exception(function() {
			(new Web\ThrowawayPart(
				$this->redis,
				new Uri\FakeUri('www.google.com'),
				'//p',
				'xpath'
			))->print(new Output\FakeFormat(''));
		}, \UnexpectedValueException::class, 'Part not found');
		Assert::same('Part for "www.google.com" URL and xpath expression "//p" not found', $ex->getPrevious()->getMessage());
	}
}

(new ThrowawayPart)->run();