<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

/**
 * Fake
 */
final class FakePart implements Part {
	private $content;
	private $refreshedPart;

	public function __construct(
		string $content = null,
		self $refreshedPart = null
	) {
		$this->content = $content;
		$this->refreshedPart = $refreshedPart;
	}

	public function content(): string {
		return $this->content;
	}

	public function refresh(): Part {
		return $this->refreshedPart ?? $this;
	}
}