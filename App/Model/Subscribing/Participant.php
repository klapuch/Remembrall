<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Output;

interface Participant {
	/**
	 * Print itself
	 * @param \Klapuch\Output\Format $format
	 * @return \Iterator
	 */
	public function print(Output\Format $format): Output\Format;
}