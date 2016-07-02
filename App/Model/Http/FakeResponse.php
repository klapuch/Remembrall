<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

/**
 * Fake
 */
final class FakeResponse implements Response {
	private $headers;
	private $content;

	public function __construct(Headers $headers = null, string $content = '') {
	    $this->headers = $headers;
		$this->content = $content;
	}

	public function headers(): Headers {
		return $this->headers;
	}

	public function content(): string {
		return $this->content;
	}
}