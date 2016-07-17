<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Model\Http;

/**
 * Part on the html page (in the html format)
 */
final class HtmlPart implements Part {
	private $expression;
	private $browser;
	private $page;

	public function __construct(
		Expression $expression,
		Http\Browser $browser,
		Page $page
	) {
		$this->expression = $expression;
		$this->browser = $browser;
		$this->page = $page;
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

	public function refresh(): Part {
		return new HtmlPart(
			new ValidXPathExpression(
				new XPathExpression(
					$this->browser->send(
						new Http\ConstantRequest(
							new Http\CaseSensitiveHeaders(
								new Http\UniqueHeaders(
									[
										'host' => $this->page->url(),
										'method' => 'GET',
									]
								)
							)
						)
					),
					(string)$this->expression
				)
			),
			$this->browser,
			$this->page
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