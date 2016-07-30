<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Integration\Subscribing;

use GuzzleHttp;
use Remembrall\Model\Subscribing;
use Remembrall\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class OnlineParts extends TestCase\Database {
	public function testIteratingWithOnlineParts() {
		$oldContent = new \DOMDocument();
		$oldContent->loadHTML('<h1>Hello</h1>');
		$oldPage = new Subscribing\FakePage($oldContent);
		$expression = new Subscribing\FakeExpression('//h1');
		$logger = $this->mockery(\Tracy\ILogger::class);
		$logger->shouldReceive('log')->never();
		$parts = (new Subscribing\OnlineParts(
			new Subscribing\FakeParts(
				[
					new Subscribing\FakePart(
						'Nevím',
						'https://nette.org',
						null,
						$expression,
						$oldPage
					),
				]
			),
			$logger,
			$this->database,
			new GuzzleHttp\Client(['http_errors' => false])
		))->iterate();
		Assert::count(1, $parts);
		Assert::same('Nevím', $parts[0]->content());
		Assert::same('<h1>Framework</h1><h1>Tracy</h1><h1>Latte</h1><h1>Tester</h1>', $parts[0]->refresh()->content());
	}

	protected function prepareDatabase() {
		$this->truncate(['pages', 'page_visits', 'parts', 'part_visits']);
		$this->restartSequence(['page_visits', 'parts', 'part_visits']);
	}
}

(new OnlineParts())->run();
