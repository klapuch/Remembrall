<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

interface Participants {
	/**
	 * Add a new participant to the subscription
	 * @param int $subscription
	 * @param string $email
	 * @return \Remembrall\Model\Subscribing\Invitation
	 */
	public function invite(int $subscription, string $email): Invitation;

	/**
	 * Go thorough all the participants
	 * @return \Iterator
	 */
	public function all(): \Iterator;
}