<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

interface VerificationCode {
	/**
	 * Use the verification code
	 * @return void
	 */
	public function use ();

	/**
	 * Who is the owner of this verification code?
	 * @return Subscriber
	 */
	public function owner(): Subscriber;
}