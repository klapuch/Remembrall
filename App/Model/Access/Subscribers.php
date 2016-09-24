<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Remembrall\Exception\DuplicateException;

interface Subscribers {
	/**
	 * Register a new subscriber to the system
	 * @param string $email
	 * @param string $password
	 * @throws DuplicateException
	 * @return Subscriber
	 */
	public function register(string $email, string $password): Subscriber;
}
