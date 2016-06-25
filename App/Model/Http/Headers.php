<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use Remembrall\Exception;

interface Headers {
	/**
	 * Go through all headers
	 * @return Header[]
	 */
	public function iterate(): array;

	/**
	 * Header by the field
	 * @throws Exception\ExistenceException
	 * @return Header
	 */
	public function header(string $field): Header;
}