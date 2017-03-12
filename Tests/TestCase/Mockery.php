<?php
declare(strict_types = 1);
namespace Remembrall\TestCase;

abstract class Mockery extends \Tester\TestCase {
	protected function mock(string $class): \Mockery\MockInterface {
		return \Mockery::mock($class);
	}

	protected function tearDown(): void {
		parent::tearDown();
		\Mockery::close();
	}
}