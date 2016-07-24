<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

final class FakeVerificationCode implements VerificationCode {
	private $owner;

	public function __construct(Subscriber $owner = null) {
	    $this->owner = $owner;
	}

	public function use() {

	}

	public function owner(): Subscriber {
		return $this->owner;
	}
}