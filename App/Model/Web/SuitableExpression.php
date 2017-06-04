<?php
declare(strict_types = 1);
namespace Remembrall\Model\Web;

use Symfony\Component\CssSelector;

/**
 * Expression using CSS or XPath language by user choice
 */
final class SuitableExpression implements Expression {
	private const LANGUAGES = ['xpath', 'css'];
	private $language;
	private $expression;
	private $page;

	public function __construct(string $language, Page $page, string $expression) {
		$this->language = $language;
		$this->page = $page;
		$this->expression = $expression;
	}

	public function matches(): \DOMNodeList {
		if (in_array($this->language, self::LANGUAGES)) {
			return (new XPathExpression(
				$this->page,
				$this->language === 'css'
					? (new CssSelector\CssSelectorConverter())->toXPath($this->expression)
					: $this->expression
			))->matches();
		}
		throw new \UnexpectedValueException(
			sprintf(
				'Allowed languages are %s - "%s" given',
				implode(
					', ',
					array_map(
						function(string $language): string {
							return sprintf('"%s"', $language);
						},
						self::LANGUAGES
					)
				),
				$this->language
			)
		);
	}
}