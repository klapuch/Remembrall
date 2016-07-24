<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

interface ForgottenPasswords {
	/**
	 * Remind forgotten password to the user by the given email
	 * @param string $email
	 * @throws \OverflowException
	 * @return RemindedPassword
	 */
	public function remind(string $email): RemindedPassword;
}