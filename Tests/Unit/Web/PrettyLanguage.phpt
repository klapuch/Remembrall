<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Web;

use Remembrall\Model\Web;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class PrettyLanguage extends Tester\TestCase {
	public function testMakingKnownLanguagePretty() {
		Assert::same('XPath', (string) new Web\PrettyLanguage('xpath'));
		Assert::same('CSS', (string) new Web\PrettyLanguage('css'));
	}

	public function testConvertingUnknownLanguageToUnknown() {
		Assert::same('unknown', (string) new Web\PrettyLanguage('fooo'));
	}
}

(new PrettyLanguage())->run();