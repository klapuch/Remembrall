<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Output;

interface Participants {
	/**
	 * Add the new participant
	 * @param string $email
	 * @return void
	 */
	public function invite(string $email): void;

	/**
	 * Remove the participant
	 * @param string $email
	 * @return void
	 */
	public function kick(string $email): void;

	/**
	 * Print itself
	 * @param \Klapuch\Output\Format $format
	 * @return \Iterator
	 */
	public function print(Output\Format $format): \Iterator;
}