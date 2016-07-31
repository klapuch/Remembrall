<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

final class FakeSubscribers implements Subscribers {
	private $subscribers;

	public function __construct(array $subscribers = []) {
	    $this->subscribers = $subscribers;
	}

	public function register(string $email, string $password): Subscriber {

	}

	public function iterate(): array {
		return $this->subscribers;
	}
}