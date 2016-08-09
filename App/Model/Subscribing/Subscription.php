<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Exception\NotFoundException;

interface Subscription {
	/**
	 * Cancel the subscription
	 * @throws NotFoundException
	 * @return void
	 */
	public function cancel();

	/**
	 * Edit the subscription
	 * Editing may not cause transformation to another part
	 * @param Interval $interval
	 * @throws NotFoundException
	 * @return Subscription
	 */
	public function edit(Interval $interval): self;

	/**
	 * Print the current subscription
	 * @return array
	 */
	public function print(): array;
}