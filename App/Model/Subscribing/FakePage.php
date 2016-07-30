<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

/**
 * Fake
 */
final class FakePage implements Page {
	private $content;
	private $refreshedPage;

	public function __construct(
		\DOMDocument $content = null,
		Page $refreshedPage = null
	) {
		$this->content = $content;
		$this->refreshedPage = $refreshedPage;
	}

	public function content(): \DOMDocument {
		return $this->content;
	}

	public function refresh(): Page {
		return $this->refreshedPage;
	}
}