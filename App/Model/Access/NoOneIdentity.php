<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Nette\Security;

final class NoOneIdentity implements Security\IIdentity {
	public function getId() {
		return 0;
	}

	public function getRoles() {
		return [];
	}
}