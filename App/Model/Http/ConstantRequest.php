<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

final class ConstantRequest implements Request {
	private $headers;

	public function __construct(Headers $headers) {
		$this->headers = $headers;
	}

	public function headers(): Headers {
		return $this->headers;
	}
}