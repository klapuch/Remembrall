<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

final class HtmlPart implements Part {
	private $page;
	private $nodes;

	public function __construct(Page $page, \DOMNodeList $nodes) {
		$this->page = $page;
		$this->nodes = $nodes;
	}

	public function source(): Page {
		return $this->page;
	}

	public function content(): string {
		return (string)array_reduce(
			iterator_to_array($this->nodes),
			function($previous, \DOMNode $node) {
				$previous .= $this->tag($node);
				return $previous;
			}
		);
	}

	public function equals(Part $part): bool {
		return $part->source()->url() === $this->source()->url()
		&& $part->content() === $this->content();
	}

	/**
	 * All tags which includes $node - also the nested ones
	 * Removes tabs and new lines (CR and LF)
	 * @param \DOMNode $node
	 * @return string
	 */
	private function tag(\DOMNode $node): string {
		$tag = sprintf(
			'<%1$s>%2$s</%1$s>',
			$node->nodeName,
			$node->childNodes && $node->childNodes->length > 0
				? (new self($this->page, $node->childNodes))->content()
				: preg_replace('~[\t\r\n]+~', '', $node->nodeValue)
		);
		return preg_replace('~<[/]*#text>~', '', $tag);
	}
}