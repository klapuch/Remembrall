<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

/**
 * Fake
 */
final class FakeHeader implements Header {
	private $field;
	private $value;
	private $equals;

	public function __construct(
		string $field,
		string $value,
		bool $equals = false
	) {
		$this->field = $field;
		$this->value = $value;
		$this->equals = $equals;
	}

	public function field(): string {
		return $this->field;
	}

	public function value(): string {
		return $this->value;
	}

	public function equals(Header $header): bool {
		return $this->equals;
	}
}