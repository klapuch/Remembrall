<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

interface VerificationCodes {
	/**
	 * Generate a new unique verification code for the given email
	 * @param string $email
	 * @return VerificationCode
	 */
	public function generate(string $email): VerificationCode;
}