<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

final class ChangedParts implements Parts {
	private $origin;

	public function __construct(Parts $origin) {
		$this->origin = $origin;
	}

	public function subscribe(Part $part, Interval $interval) {
		$this->origin->subscribe($part, $interval);
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
						)
					)
				);
			}
		);
	}
}
