<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

/**
 * Fake
 */
final class FakeSubscribers implements Subscribers {
	public function register(string $email, string $password): Subscriber {
	}
}