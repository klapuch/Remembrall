<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Nette\Security;

final class IdentityFactory {
	private $user;

	public function __construct(Security\User $user) {
		$this->user = $user;
	}

	public function create(): Security\IIdentity {
		if($this->user->getIdentity() === null)
			return new NoOneIdentity();
		return $this->user->getIdentity();
	}
}