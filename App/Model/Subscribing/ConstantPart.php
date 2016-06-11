<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

final class ConstantPart implements Part {
	private $source;
	private $content;

	public function __construct(Page $source, string $content) {
		$this->source = $source;
		$this->content = $content;
	}

	public function source(): Page {
		return $this->source;
	}

	public function content(): string {
		return $this->content;
	}

	public function equals(Part $part): bool {
		return $part->source()->url() === $this->source()->url()
		&& $part->content() === $this->content();
	}
}