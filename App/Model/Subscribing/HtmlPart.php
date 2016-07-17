<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

/**
 * Part on the html page (in the html format)
 */
final class HtmlPart implements Part {
	private $expression;

	public function __construct(Expression $expression) {
		$this->expression = $expression;
	}

	public function content(): string {
		return (string)array_reduce(
			iterator_to_array($this->expression->match()),
			function($previous, \DOMNode $node) {
				$previous .= preg_replace(
					'~[\t\r\n]+~', // removes tabs and new lines (CR and LF)
					'',
					$node->ownerDocument->saveHTML($node)
				);
				return $previous;
			}
		);
	}

	public function equals(Part $part): bool {
		return $part->content() === $this->content();
	}

	public function print(): array {
		return [
			'expression' => $this->expression,
		];
	}
}