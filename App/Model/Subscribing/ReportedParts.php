<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

final class ReportedParts implements Parts {
	private $origin;
	private $reports;

	public function __construct(Parts $origin, Reports $reports) {
		$this->origin = $origin;
		$this->reports = $reports;
	}

	public function subscribe(Part $part, Interval $interval): Part {
		$subscribedPart = $this->origin->subscribe($part, $interval);
		$this->reports->archive($subscribedPart);
		return $subscribedPart;
	}

	public function replace(Part $old, Part $new) {
		$this->origin->replace($old, $new);
		$this->reports->archive($new);
	}

	public function remove(Part $part) {
		$this->origin->remove($part);
	}

	public function iterate(): array {
		return $this->origin->iterate();
	}
}
