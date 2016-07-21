<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

/**
 * Fake
 */
final class FakeResponse implements Response {
	private $content;

	public function __construct(string $content = null) {
		$this->content = $content;
	}

	public function content(): string {
		return $this->content;
	}
}