<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

/**
 * Fake
 */
final class FakePage implements Page {
	private $content;
	private $refreshedPart;

	public function __construct(
		\DOMDocument $content = null,
		Page $refreshedPart = null
	) {
		$this->content = $content;
		$this->refreshedPart = $refreshedPart;
	}

	public function content(): \DOMDocument {
		return $this->content;
	}

	public function refresh(): Page {
		return $this->refreshedPart;
	}
}