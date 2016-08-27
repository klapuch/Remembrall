<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Remembrall\Unit\Access;

use Remembrall\Model\Access;
use Tester\Assert;
use Remembrall\TestCase;
use Nette\Security;

require __DIR__ . '/../../bootstrap.php';

final class IdentityFactory extends TestCase\Mockery {
	public function testNoOneIdentity() {
		$user = $this->mockery(Security\User::class);
		$user->shouldReceive('getIdentity')
			->andReturnNull();
		Assert::type(
			Access\NoOneIdentity::class,
			(new Access\IdentityFactory($user))->create()
		);
	}

	public function testOwnedIdentity() {
		$identity = new Security\Identity(1);
		$user = $this->mockery(Security\User::class);
		$user->shouldReceive('getIdentity')
			->andReturn($identity);
		Assert::type(
			$identity,
			(new Access\IdentityFactory($user))->create()
		);
	}
}

(new IdentityFactory())->run();
