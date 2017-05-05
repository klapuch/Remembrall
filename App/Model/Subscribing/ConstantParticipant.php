<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Output;

final class ConstantParticipant implements Participant {
	private $participant;

	public function __construct(array $participant) {
	    $this->participant = $participant;
	}

	public function print(Output\Format $format): Output\Format {
		return new Output\FilledFormat($format, $this->participant);
	}
}