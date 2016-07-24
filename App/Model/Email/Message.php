<?php
declare(strict_types = 1);
namespace Remembrall\Model\Email;

use Remembrall\Model\Access;

interface Message {
	/**
	 * Email of the sender of the message
	 * @return string
	 */
	public function sender(): string;

	/**
	 * Collection of all subscribers which will receive the message
	 * @return Access\Subscribers
	 */
	public function recipients(): Access\Subscribers;

	/**
	 * Subject of the message
	 * @return string
	 */
	public function subject(): string;

	/**
	 * Content of the message
	 * @return string
	 */
	public function content(): string;
}