<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

interface Participants {
	/**
	 * Add the new participant to the subscription
	 * @param int $subscription
	 * @param string $email
	 * @return void
	 */
	public function invite(int $subscription, string $email): void;

	/**
	 * Remove the participant from the given subscription
	 * @param int $subscription
	 * @param string $email
	 * @return void
	 */
	public function kick(int $subscription, string $email): void;

	/**
	 * Go thorough all the participants
	 * @return \Iterator
	 */
	public function all(): \Iterator;
}