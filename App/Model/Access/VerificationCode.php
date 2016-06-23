<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

interface VerificationCode {
	/**
	 * Use the verification code
	 * @return VerificationCode
	 */
	public function use(): self;

	/**
	 * Who is the owner of this verification code?
	 * @return Subscriber
	 */
	public function owner(): Subscriber;
}