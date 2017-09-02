<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Output;

/**
 * Fake
 */
final class FakeParticipant implements Participant {
	private $print;

	public function __construct(Output\Format $print = null) {
		$this->print = $print;
	}

	public function kick(): void {
	}

	public function print(Output\Format $format): Output\Format {
		return $this->print;
	}
}