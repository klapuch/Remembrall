<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

/**
 * Constant page without roundtrips
 */
final class ConstantPage implements Page {
	private $url;
	private $content;

	public function __construct(string $url, string $content) {
		$this->url = $url;
		$this->content = $content;
	}

	public function content(): \DOMDocument {
		$dom = new DOM();
		$dom->loadHTML($this->content);
		return $dom;
	}

	public function equals(Page $page): bool {
		return $page->equals($this);
	}

	public function url(): string {
		return $this->url;
	}
}