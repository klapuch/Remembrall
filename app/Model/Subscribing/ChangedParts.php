<?php
declare(strict_types = 1);
namespace Remembrall\Model\Subscribing;

use Remembrall\Exception;
use Remembrall\Model\Http;

/**
 * Parts which differ from the parts on the internet
 */
final class ChangedParts implements Parts {
	private $origin;
	private $browser;

	public function __construct(Parts $origin, Http\Browser $browser) {
		$this->origin = $origin;
		$this->browser = $browser;
	}

	public function subscribe(
		Part $part,
		string $url,
		string $expression,
		Interval $interval
	): Part {
		if(!$this->changed($part, $url, $expression)) {
			throw new Exception\NotFoundException(
				'The part has not changed yet'
			);
		}
		/**
		 * Part may never be HtmlPart, because they would be the same
		 */
		return $this->origin->subscribe(
			new HtmlPart(
				new ValidXPathExpression(
					new XPathExpression(
						$this->browser->send(
							new Http\ConstantRequest(
								new Http\CaseSensitiveHeaders(
									new Http\UniqueHeaders(
										[
											'host' => $url,
											'method' => 'GET',
										]
									)
								)
							)
						),
						$expression
					)
				)
			),
			$url,
			$expression,
			$interval
		);
	}

	public function remove(string $url, string $expression) {
		$this->origin->remove($url, $expression);
	}

	public function iterate(): array {
		return array_filter(
			$this->origin->iterate(),
			function(Part $part) {
				$visualizedPart = $part->print();
				return $this->changed(
					$part,
					$visualizedPart['page']->url(),
					(string)$visualizedPart['expression']
				);
			}
		);
	}

	/**
	 * Download page from the internet and check whether the change has occurred
	 * @param Part $part
	 * @param string $url
	 * @param string $expression
	 * @return bool
	 */
	private function changed(Part $part, string $url, string $expression) {
		return !$part->equals(
			new HtmlPart(
				new ValidXPathExpression(
					new XPathExpression(
						$this->browser->send(
							new Http\ConstantRequest(
								new Http\CaseSensitiveHeaders(
									new Http\UniqueHeaders(
										[
											'host' => $url,
											'method' => 'GET',
										]
									)
								)
							)
						),
						$expression
					)
				)
			)
		);
	}
}
