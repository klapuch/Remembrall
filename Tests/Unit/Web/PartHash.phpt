<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Web;

use Klapuch\Uri;
use Remembrall\Model\Web;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class PartHash extends Tester\TestCase {
	public function testAlphaNumericHash() {
		[$expression, $language, $url] = ['//p', 'xpath', new Uri\FakeUri('www.google.com')];
		Assert::match('%h%', (string) new Web\PartHash($url, $expression, $language));
	}

	public function testHashNotUsingRandomness() {
		[$expression, $language, $url] = ['//p', 'xpath', new Uri\FakeUri('www.google.com')];
		Assert::same(
			(string) new Web\PartHash($url, $expression, $language),
			(string) new Web\PartHash($url, $expression, $language)
		);
	}

	public function testHashUsingScalars() {
		[$expression, $language] = ['//p', 'xpath'];
		Assert::same(
			(string) new Web\PartHash(new Uri\FakeUri('www.google.com'), $expression, $language),
			(string) new Web\PartHash(new Uri\FakeUri('www.google.com'), $expression, $language)
		);
	}

	public function testDifferentParts() {
		[$expression, $language] = ['//p', 'xpath'];
		Assert::notSame(
			(string) new Web\PartHash(new Uri\FakeUri('www.google.com'), $expression, $language),
			(string) new Web\PartHash(new Uri\FakeUri('www.seznam.cz'), $expression, $language)
		);
	}
}

(new PartHash())->run();