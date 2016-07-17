<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

/**
 * Text part without tags or other elements
 */
final class TextPart implements Part {
	private $origin;

	public function __construct(Part $origin) {
		$this->origin = $origin;
	}

	public function content(): string {
		return strip_tags($this->origin->content());
	}

	public function equals(Part $part): bool {
		return $this->origin->equals($part);
	}

	public function print(): array {
		return $this->origin->print();
	}
}