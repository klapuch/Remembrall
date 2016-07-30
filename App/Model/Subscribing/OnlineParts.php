<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Model\Http;

/**
 * Online parts on the internet, it means, they are freshly downloaded
 */
final class OnlineParts implements Parts {
	private $origin;
	private $request;

	public function __construct(Parts $origin, Http\Request $request) {
		$this->origin = $origin;
		$this->request = $request;
	}

	public function add(Part $part, string $url, string $expression): Part {
		$this->origin->add($part, $url, $expression);
	}

	public function iterate(): array {
		return array_reduce(
			$this->origin->iterate(),
			function($previous, Part $part) {
				$visualPart = $part->print();
				/**
				 * @var string $url
				 * @var string $expression
				 * @var Page $oldPage
				 */
				list($url, $expression, $oldPage) = [
					$visualPart['url'],
					(string)$visualPart['expression'],
					$visualPart['page']
				];
				$onlinePage = $this->request->send();
				$previous[] = new ConstantPart(
					new HtmlPart(
						new XPathExpression(
							new ConstantPage(
								$onlinePage,
								$oldPage->content()->saveHTML()
							),
							$expression
						),
						new ConstantPage(
							$onlinePage,
							$oldPage->content()->saveHTML()
						)
					),
					$part->content(),
					$url
				);
				return $previous;
			}
		);
	}
}
