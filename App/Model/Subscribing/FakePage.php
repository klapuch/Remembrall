<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

/**
 * Fake
 */
final class FakePage implements Page {
	private $url;
	private $content;
	private $equals;

	public function __construct(
		string $url = null,
		\DOMDocument $content = null,
		$equals = false
	) {
		$this->url = $url;
		$this->content = $content;
		$this->equals = $equals;
	}

	public function content(): \DOMDocument {
		return $this->content;
	}

	public function url(): string {
		return $this->url;
	}

	public function equals(Page $page): bool {
		return $this->equals;
	}
}