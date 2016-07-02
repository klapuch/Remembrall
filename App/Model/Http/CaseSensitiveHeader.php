<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

/**
 * Case sensitive header where does not depend on upper case or lower case chars
 */
final class CaseSensitiveHeader implements Header {
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

	public function equals(Header $header): bool {
		$sameField = strcasecmp($this->field(), $header->field()) === 0;
		$sameValue = strcasecmp($this->value(), $header->value()) === 0;
		if(!$sameValue && $sameField) {
			foreach(array_map('trim', explode(';', $this->value())) as $value)
				if(strcasecmp($value, $header->value()) === 0)
					return true;
			return false;
		}
		return $sameField && $sameValue;
	}
}