<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

final class FakeForgottenPasswords implements ForgottenPasswords {
	public function remind(string $email): RemindedPassword {
		return new FakeRemindedPassword();
	}
}