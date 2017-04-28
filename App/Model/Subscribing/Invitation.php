<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Output;

interface Invitation {
	/**
	 * Accept the invitation
	 * @return void
	 */
	public function accept(): void;

	/**
	 * Decline the invitation
	 * @return void
	 */
	public function decline(): void;

	/**
	 * Print itself
	 * @param \Klapuch\Output\Format $format
	 * @return \Klapuch\Output\Format
	 */
	public function print(Output\Format $format): Output\Format;
}