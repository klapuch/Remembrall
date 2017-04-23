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
	 * Deny the invitation
	 * @return void
	 */
	public function deny(): void;

	/**
	 * Print itself
	 * @param \Klapuch\Output\Format $format
	 * @return \Klapuch\Output\Format
	 */
	public function print(Output\Format $format): Output\Format;
}