<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

final class HtmlPart implements Part {
	private $page;
	private $expression;

	public function __construct(Page $page, Expression $expression) {
		$this->page = $page;
		$this->expression = $expression;
	}

	public function source(): Page {
		return $this->page;
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
		return $part->source()->url() === $this->source()->url()
		&& $part->content() === $this->content();
	}

	public function expression(): Expression {
		return $this->expression;
	}
}