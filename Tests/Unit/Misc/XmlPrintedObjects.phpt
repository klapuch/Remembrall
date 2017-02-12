<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Remembrall\Unit\Misc;

use Klapuch\Output;
use Remembrall\Model\Misc;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class XmlPrintedObjects extends Tester\TestCase {
	public function testWrapping() {
		Assert::same(
			'<parts><part><foo>bar</foo></part><part><bar>foo</bar></part></parts>',
			(new Misc\XmlPrintedObjects(
				'parts',
				[
					'part' => [
						new class {
							function print(Output\Format $format) {
								return $format->with('foo', 'bar');
							}
						},
						new class {
							function print(Output\Format $format) {
								return $format->with('bar', 'foo');
							}
						}
					]
				]
			))->serialization()
		);
	}

	/**
	 * @throws \Exception Not implemented
	 */
	public function testDisabledWithMethod() {
		(new Misc\XmlPrintedObjects('', []))->with('foo', 'bar');
	}

	/**
	 * @throws \Exception Not implemented
	 */
	public function testDisabledAdjustedMethod() {
		(new Misc\XmlPrintedObjects('', []))->adjusted('foo', 'trim');
	}
}

(new XmlPrintedObjects())->run();