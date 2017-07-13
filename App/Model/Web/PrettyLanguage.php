<?php
declare(strict_types = 1);
namespace Remembrall\Model\Web;

/**
 * Language made pretty
 */
final class PrettyLanguage {
	private const LANGUAGES = ['xpath' => 'XPath', 'css' => 'CSS'];
	private $language;

	public function __construct(string $language) {
		$this->language = $language;
	}

	public function __toString(): string {
		return self::LANGUAGES[$this->language] ?? 'unknown';
	}
}