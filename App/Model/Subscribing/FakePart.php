<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

final class FakePart implements Part {
	private $source;
	private $content;
	private $equals;

	public function __construct(
		string $content = null,
		Page $source = null,
		bool $equals = false
	) {
		$this->source = $source;
		$this->content = $content;
		$this->equals = $equals;
	}

	public function source(): Page {
		return $this->source;
	}

	public function content(): string {
		return $this->content;
	}

	public function equals(Part $part): bool {
		return $this->equals;
	}
}