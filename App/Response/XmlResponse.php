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
					new \Exception(
						implode(
							' | ',
							array_map(
								function(\LibXMLError $error): string {
									return trim($error->message);
								},
								libxml_get_errors()
							)
						)
					)
				);
			}
			return $body;
		} finally {
			libxml_use_internal_errors($previous);
		}
	}

	public function headers(): array {
		http_response_code($this->code);
		return self::HEADERS + array_change_key_case($this->origin->headers(), CASE_LOWER);
	}
}