<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Access;

use Remembrall\Model\Access;
use Tester\Assert;
use Remembrall\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class NoOneIdentity extends TestCase\Mockery {
    public function testId() {
        Assert::same(0, (new Access\NoOneIdentity())->getId());
    }

    public function testRoles() {
        Assert::same([], (new Access\NoOneIdentity())->getRoles());
    }

}

(new NoOneIdentity())->run();
