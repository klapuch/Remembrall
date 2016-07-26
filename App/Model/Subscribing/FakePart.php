<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

/**
 * Fake
 */
final class FakePart implements Part {
	private $content;
	private $equals;
	private $url;

	public function __construct(
		string $content = null,
		bool $equals = false,
		string $url = null
	) {
		$this->content = $content;
		$this->equals = $equals;
		$this->url = $url;
	}

	public function content(): string {
		return $this->content;
	}

	public function equals(Part $part): bool {
		return $this->equals;
	}

	public function refresh(): Part {
		return $this;
	}
}