<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

interface Subscriber {
	/**
	 * ID of the subscriber
	 * @return int
	 */
	public function id(): int;

	/**
	 * Email of the subscriber
	 * @return string
	 */
	public function email(): string;
}