<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use Remembrall\Exception;

final class UniqueHeaders implements Headers {
	private $headers;

	public function __construct(array $headers) {
		$this->headers = $headers;
	}

	public function iterate(): array {
		return array_reduce(
			array_keys($this->headers),
			function($previous, $field) {
				if($this->headers[$field] instanceof Header) {
					$header = $this->headers[$field];
					$previous[$header->field()] = $header;
				} elseif(is_string($this->headers[$field])) {
					$previous[$field] = new CaseSensitiveHeader(
						$field,
						$this->headers[$field]
					);
				}
				return $previous;
			}
		);
	}

	public function header(string $field): Header {
		$headers = $this->iterate();
		if(isset($headers[$field]))
			return $headers[$field];
		throw new Exception\ExistenceException(
			sprintf('Header "%s" does not exist', $field)
		);
	}

	public function included(Header $header): bool {
		return (bool)array_filter(
			$this->iterate(),
			function(Header $includedHeader) use($header) {
				return $includedHeader->equals($header);
			}
		);
	}
}