<?php
declare(strict_types = 1);
namespace Remembrall\Model\Access;

interface Subscriber {
	/**
	 * Id of the subscriber
	 * @return int
	 */
	public function id(): int;

	/**
	 * Email of the subscriber
	 * @return string
	 */
	public function email(): string;
}
