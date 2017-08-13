<?php
declare(strict_types = 1);
namespace Remembrall\Response;

use Klapuch\Application;
use Klapuch\Output;

/**
 * Validated XML response
 */
final class XmlResponse implements Application\Response {
	private const HEADERS = ['content-type' => 'text/xml; charset=utf8'];
	private const OK = 200;
	private $origin;
	private $code;

	public function __construct(
		Application\Response $origin,
		int $code = self::OK
	) {
		$this->origin = $origin;
		$this->code = $code;
	}

	public function body(): Output\Format {
		$previous = libxml_use_internal_errors(true);
		try {
			$body = $this->origin->body();
			$xml = simplexml_load_string($body->serialization());
			if ($xml === false) {
				throw new \UnexpectedValueException(
					'XML document is not valid',
					0,
					new \Exception($this->error(...libxml_get_errors()))
				);
			}
			return new class($body, $xml) implements Output\Format {
				private $origin;
				private $xml;

				public function __construct(Output\Format $origin, \SimpleXMLElement $xml) {
					$this->origin = $origin;
					$this->xml = $xml;
				}

				public function with($tag, $content = null): Output\Format {
					return $this->origin->with($tag, $content);
				}

				public function serialization(): string {
					$dom = new \DOMDocument('1.0', 'utf-8');
					$dom->appendChild(
						$dom->importNode(
							dom_import_simplexml($this->xml),
							true
						)
					);
					return $dom->saveXML();
				}

				public function adjusted($tag, callable $adjustment): Output\Format {
					return $this->origin->adjusted($tag, $adjustment);
				}
			};
		} finally {
			libxml_use_internal_errors($previous);
		}
	}

	public function headers(): array {
		http_response_code($this->code);
		return self::HEADERS + array_change_key_case($this->origin->headers(), CASE_LOWER);
	}

	private function error(\LibXMLError ...$errors): string {
		return implode(
			' | ',
			array_map(
				function(\LibXMLError $error): string {
					return trim($error->message);
				},
				$errors
			)
		);
	}
}