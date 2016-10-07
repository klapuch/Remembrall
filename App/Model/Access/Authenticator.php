<?php
namespace Remembrall\Model\Access;

use Klapuch\Access;
use Nette\Security;
use Nette\Security\AuthenticationException;

final class Authenticator implements Security\IAuthenticator {
	private $entrance;

	public function __construct(Access\Entrance $entrance) {
		$this->entrance = $entrance;
	}

    public function authenticate(array $credentials) {
        try {
            $user = $this->entrance->entry($credentials);
            return new Security\Identity($user->id());
        } catch(\Exception $ex) {
            throw new AuthenticationException(
                'The password or email is incorrect',
                $ex->getCode(),
                $ex
            );
        }
	}
}