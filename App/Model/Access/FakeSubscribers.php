<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

use Remembrall\Exception;

final class FakeSubscribers implements Subscribers {
	public function register(string $email, string $password): Subscriber {

	}

	public function iterate(): array {
		
	}
}