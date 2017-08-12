<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Functional\Page\Parts;

use Klapuch\Application;
use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Uri;
use Remembrall\Misc;
use Remembrall\Page\Parts;
use Remembrall\Response;
use Remembrall\TestCase;
use Tester\Assert;
use Tester\DomQuery;

require __DIR__ . '/../../../bootstrap.php';

final class PopularPage extends \Tester\TestCase {
	use TestCase\Page;

	public function testWorkingRendering() {
		Assert::same(
			'Popular parts',
			(string) DomQuery::fromHtml(
				(new Misc\TestTemplate(
					(new Parts\PopularPage(
						new Uri\FakeUri('', '/sign/in'),
						new Log\FakeLogs(),
						new Ini\FakeSource($this->configuration)
					))->template([])
				))->render()
			)->find('h1')[0]
		);
	}

	public function testRedirectingOnError() {
		(new Misc\TestUsers($this->database))->register();
		$_GET['sort'] = 'foo';
		Assert::equal(
			new Application\HtmlTemplate(
				new Response\InformativeResponse(
					new Response\RedirectResponse(
						new Response\EmptyResponse(),
						new Uri\FakeUri('', 'parts/popular')
					),
					['danger' => 'Following criteria are not allowed: "foo"'],
					$_SESSION
				)
			),
			(new Parts\PopularPage(
				new Uri\FakeUri('', 'parts/popular'),
				new Log\FakeLogs(),
				new Ini\FakeSource($this->configuration)
			))->template([])
		);
	}
}

(new PopularPage())->run();