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
		return array_reduce(
			array_keys($this->participant),
			function(Output\Format $format, string $name): Output\Format {
				return $format->with($name, $this->participant[$name] === null ? '' : $this->participant[$name]);
			},
			$format
		);
	}
}