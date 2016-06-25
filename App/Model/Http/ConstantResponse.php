<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use GuzzleHttp;

final class ConstantResponse implements Response {
	private $headers;
	private $content;

	public function __construct(Headers $headers, string $content) {
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