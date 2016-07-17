<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

/**
 * Constant page without roundtrips
 */
final class ConstantPage implements Page {
	private $content;
	private $url;

	public function __construct(string $content, string $url) {
		$this->content = $content;
		$this->url = $url;
	}

	public function content(): \DOMDocument {
		$dom = new DOM();
		$dom->loadHTML($this->content);
		return $dom;
	}

	public function url(): string {
		return $this->url;
	}
}