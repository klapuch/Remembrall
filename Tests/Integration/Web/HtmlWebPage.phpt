<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Integration\Web;

use Klapuch\Http;
use Klapuch\Uri;
use Remembrall\Model\Web;
use Tester;

require __DIR__ . '/../../bootstrap.php';

final class HtmlWebPage extends Tester\TestCase {
	/**
	 * @throws \UnexpectedValueException Error during requesting the page.
	 */
	public function testThrowingOnRequestError() {
		(new Web\HtmlWebPage(
			new Http\BasicRequest('GET', new Uri\FakeUri('xxx://www.google.com'))
		))->content();
	}
}

(new HtmlWebPage())->run();