<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Exception;

/**
 * Only the changed parts
 */
final class ChangedParts implements Parts {
	private $origin;

	public function __construct(Parts $origin) {
		$this->origin = $origin;
	}

	public function subscribe(Part $part, Interval $interval): Part {
		return $this->origin->subscribe($part, $interval);
	}

	public function replace(Part $old, Part $new) {
		if(!$this->changed($old)) {
			throw new Exception\ExistenceException(
				'This part has not changed yet'
			);
		}
		$this->origin->replace($old, $new);
	}

	public function remove(Part $part) {
		$this->origin->remove($part);
	}

	public function iterate(): array {
		return array_filter(
			$this->origin->iterate(),
			function(Part $part) {
				return !$part->equals(
					new HtmlPart(
						$part->source(),
						new XPathExpression(
							$part->source(),
							(string)$part->expression()
						),
						$part->owner()
					)
				);
			}
		);
	}

	/**
	 * Checks whether the given part is really changed
	 * @param Part $part
	 * @return bool
	 */
	private function changed(Part $part): bool {
		return (bool)array_filter(
			$this->iterate(),
			function(Part $changedPart) use ($part) {
				return $part->equals($changedPart);
			}
		);
	}
}
