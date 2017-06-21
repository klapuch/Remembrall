<?php
declare(strict_types = 1);
namespace Remembrall\Model\Web;

use Klapuch\Uri;

/**
 * Hash identifying uniqueness of part
 */
final class PartHash {
	private $uri;
	private $expression;
	private $language;

	public function __construct(Uri\Uri $uri, string $expression, string $language) {
		$this->uri = $uri;
		$this->expression = $expression;
		$this->language = $language;
	}

	public function __toString(): string {
		return sha1($this->uri->reference() . $this->expression . $this->language);
	}
}