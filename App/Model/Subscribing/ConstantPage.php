<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

/**
 * Constant page without roundtrips
 */
final class ConstantPage implements Page {
	private $origin;
	private $content;

	public function __construct(Page $origin, string $content) {
		$this->origin = $origin;
		$this->content = $content;
	}

	public function content(): \DOMDocument {
		$dom = new DOM();
		$dom->loadHTML($this->content);
		return $dom;
	}

	public function refresh(): Page {
		return $this->origin->refresh();
	}
}