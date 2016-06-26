<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use Remembrall\Exception;

final class FakeHeaders implements Headers {
	private $headers;
	private $included;

	public function __construct(array $headers = [], bool $included = false) {
		$this->headers = $headers;
		$this->included = $included;
	}

	public function iterate(): array {
		$headers = [];
		foreach($this->headers as $field => $value)
			$headers[$field] = new FakeHeader($field, $value, $this->included);
		return $headers;
	}

	public function header(string $field): Header {
		return $this->iterate()[$field];
	}

	public function included(Header $header): bool {
		return $this->included;
	}
}