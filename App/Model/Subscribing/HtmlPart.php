<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Klapuch\Output;

/**
 * Part on the html page (in the html format)
 */
final class HtmlPart implements Part {
    const EMPTY_PART = '';
	private $expression;
	private $page;

	public function __construct(Expression $expression, Page $page) {
		$this->expression = $expression;
		$this->page = $page;
	}

	public function content(): string {
		return array_reduce(
			iterator_to_array($this->expression->matches()),
            function($previous, \DOMNode $node) {
                $previous .= $this->withoutWhiteSpaces(
                    $node->ownerDocument->saveHTML($node)
                );
				return $previous;
            },
            self::EMPTY_PART
		);
	}

	public function refresh(): Part {
		return new self($this->expression, $this->page->refresh());
	}

	public function print(Output\Format $format): Output\Format {
		return $format->with('expression', $this->expression);
    }

    /**
     * Html without tabs and new lines (CR and LF)
     * @param string $html
     * @return string
     */
    private function withoutWhiteSpaces(string $html): string {
        return preg_replace('~[\t\r\n]+~', '', $html);
    }
}
