<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Output;
use Klapuch\Time;

interface Subscription {
	/**
	 * Cancel the subscription
	 * @throws \Remembrall\Exception\NotFoundException
	 * @return void
	 */
	public function cancel(): void;

	/**
	 * Edit the subscription
	 * Editing must not cause transformation to another part
	 * @param \Klapuch\Time\Interval $interval
	 * @throws \Remembrall\Exception\NotFoundException
	 * @return void
	 */
	public function edit(Time\Interval $interval): void;

	/**
	 * Send notification about changes on the current subscription
	 * @throws \Remembrall\Exception\NotFoundException
	 * @return void
	 */
	public function notify(): void;

	/**
	 * Print itself
	 * @param \Klapuch\Output\Format $format
	 * @return \Klapuch\Output\Format
	 */
	public function print(Output\Format $format): Output\Format;
}