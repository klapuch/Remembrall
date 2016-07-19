<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Exception;

/**
 * Parts which differ from the parts on the internet
 */
final class ChangedParts implements Parts {
	private $origin;

	public function __construct(Parts $origin) {
		$this->origin = $origin;
	}

	public function subscribe(
		Part $part,
		string $url,
		string $expression,
		Interval $interval
	): Part {
		if(!$this->changed($part)) {
			throw new Exception\NotFoundException(
				'The part has not changed yet'
			);
		}
		return $this->origin->subscribe(
			$part->refresh(),
			$url,
			$expression,
			$interval
		);
	}

	public function remove(string $url, string $expression) {
		$this->origin->remove($url, $expression);
	}

	public function iterate(): array {
		return array_filter(
			$this->origin->iterate(),
			function(Part $part): bool {
				return $this->changed($part);
			}
		);
	}

	/**
	 * Refresh the part and check whether the change has occurred
	 * @param Part $part
	 * @return bool
	 */
	private function changed(Part $part) {
		return !$part->equals($part->refresh());
	}
}
