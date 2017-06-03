<?php
declare(strict_types = 1);
namespace Remembrall\Model\Web;

use Symfony\Component\CssSelector;

/**
 * Expression using CSS or XPath selector by user choice
 */
final class SuitableExpression implements Expression {
	private const CHOICES = ['xpath', 'css'];
	private $choice;
	private $expression;
	private $page;

	public function __construct(string $choice, Page $page, string $expression) {
		$this->choice = $choice;
		$this->page = $page;
		$this->expression = $expression;
	}

	public function matches(): \DOMNodeList {
		if (in_array($this->choice, self::CHOICES)) {
			return (new XPathExpression(
				$this->page,
				$this->choice === 'css'
					? (new CssSelector\CssSelectorConverter())->toXPath($this->expression)
					: $this->expression
			))->matches();
		}
		throw new \UnexpectedValueException(
			sprintf(
				'Allowed choices are %s - "%s" given',
				implode(
					', ',
					array_map(
						function(string $choice): string {
							return sprintf('"%s"', $choice);
						},
						self::CHOICES
					)
				),
				$this->choice
			)
		);
	}

	public function __toString(): string {
		return $this->expression;
	}
}