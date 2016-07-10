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
	 * @param string $field
	 * @throws Exception\ExistenceException
	 * @return Header
	 */
	public function header(string $field): Header;

	/**
	 * Is the given header included in the headers?
	 * @param Header $header
	 * @return bool
	 */
	public function included(Header $header): bool;
}