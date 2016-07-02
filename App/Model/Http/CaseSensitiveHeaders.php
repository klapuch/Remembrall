<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use Remembrall\Exception;

/**
 * Collection of case sensitive headers
 */
final class CaseSensitiveHeaders implements Headers {
	private $origin;

	public function __construct(Headers $origin) {
		$this->origin = $origin;
	}

	public function iterate(): array {
		return $this->origin->iterate();
	}

	public function header(string $field): Header {
		$header = current(
			array_filter(
				$this->iterate(),
				function(Header $header) use ($field) {
					return strcasecmp($header->field(), $field) === 0;
				}
			)
		);
		if(empty($header)) {
			throw new Exception\ExistenceException(
				sprintf('Header "%s" does not exist', $field)
			);
		}
		return $header;
	}

	public function included(Header $header): bool {
		return $this->origin->included($header);
	}
}