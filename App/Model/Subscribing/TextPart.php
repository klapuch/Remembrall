<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

final class TextPart implements Part {
	private $origin;

	public function __construct(Part $origin) {
		$this->origin = $origin;
	}

	public function source(): Page {
		return $this->origin->source();
	}

	public function content(): string {
		return strip_tags($this->origin->content());
	}

	public function equals(Part $part): bool {
		return $part->source()->url() === $this->source()->url()
		&& $part->content() === $this->content();
	}
}