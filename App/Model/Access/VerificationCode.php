<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Remembrall\Exception;

interface VerificationCode {
	/**
	 * Use the verification code
	 * @throws Exception\NotFoundException
	 * @return void
	 */
	public function use();

	/**
	 * Who is the owner of this verification code?
	 * @throws Exception\NotFoundException
	 * @return Subscriber
	 */
	public function owner(): Subscriber;
}