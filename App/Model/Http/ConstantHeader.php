<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

final class ConstantHeader implements Header {
	private $field;
	private $value;

	public function __construct(string $field, string $value) {
		$this->field = $field;
		$this->value = $value;
	}

	public function field(): string {
		return $this->field;
	}

	public function value(): string {
		return $this->value;
	}
}