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

	public function add(Part $part, string $url, string $expression): Part {
		if(!$this->changed($part)) {
			throw new Exception\NotFoundException(
				'The part has not changed yet'
			);
		}
		return $this->origin->add($part->refresh(), $url, $expression);
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
	 * Is the refreshed part as the current part?
	 * @param Part $part
	 * @return bool
	 */
	private function changed(Part $part) {
		return !$part->equals($part->refresh());
	}
}
