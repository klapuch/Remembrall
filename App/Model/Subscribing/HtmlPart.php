<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Output;

/**
 * Part in HTML format
 */
final class HtmlPart implements Part {
	private const EMPTY_PART = '';
	private $expression;
	private $page;

	public function __construct(Expression $expression, Page $page) {
		$this->expression = $expression;
		$this->page = $page;
	}

	public function snapshot(): string {
		return sha1($this->content());
	}

	public function content(): string {
		return array_reduce(
			iterator_to_array($this->expression->matches()),
			function(string $parts, \DOMNode $part): string {
				$parts .= $this->unify(
					$part->ownerDocument->saveHTML($part)
				);
				return $parts;
			},
			self::EMPTY_PART
		);
	}

	public function refresh(): Part {
		return new self($this->expression, $this->page->refresh());
	}

	public function print(Output\Format $format): Output\Format {
		return $format;
	}

	/**
	 * HTML without tabs and new lines (CR and LF)
	 * @param string $html
	 * @return string
	 */
	private function unify(string $html): string {
		return preg_replace('~[\t\r\n]+~', '', $html);
	}
}