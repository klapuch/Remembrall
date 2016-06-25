<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use Remembrall\Exception;

final class FakeHeaders implements Headers {
	private $headers;

	public function __construct(array $headers) {
		$this->headers = $headers;
	}

	public function iterate(): array {
		$headers = [];
		foreach($this->headers as $field => $value)
			$headers[$field] = new ConstantHeader($field, $value);
		return $headers;
	}

	public function header(string $field): Header {
		return $this->iterate()[$field];
	}
}