<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Time;
use Remembrall\Exception\NotFoundException;

interface Subscription {
	/**
	 * Cancel the subscription
	 * @throws NotFoundException
	 * @return void
	 */
	public function cancel(): void;

	/**
	 * Edit the subscription
	 * Editing must not cause transformation to another part
	 * @param \Klapuch\Time\Interval $interval
	 * @throws NotFoundException
	 * @return void
	 */
    public function edit(Time\Interval $interval): void;

    /**
     * Send notification about changes on the current subscription
     * @throws NotFoundException
     * @return void
     */
    public function notify(): void;
}
