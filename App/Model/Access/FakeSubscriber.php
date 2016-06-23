<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

final class FakeSubscriber implements Subscriber {
	private $id;
	private $email;

	public function __construct(int $id = null, string $email = null) {
		$this->id = $id;
		$this->email = $email;
	}

	public function id(): int {
		return $this->id;
	}

	public function email(): string {
		return $this->email;
	}
}