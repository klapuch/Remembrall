<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use Remembrall\Exception;

final class FakeHeaders implements Headers {
	private $headers;

	public function __construct(array $headers = []) {
		$this->headers = $headers;
	}

	public function iterate(): array {
		$headers = [];
		foreach($this->headers as $field => $value)
			$headers[$field] = new FakeHeader($field, $value);
		return $headers;
	}

	public function header(string $field): Header {
		return $this->iterate()[$field];
	}

	public function included(Header $header): bool {
		if(isset($this->headers[$header->field()]))
			return $this->headers[$header->field()] === $header->value();
		return false;
	}
}