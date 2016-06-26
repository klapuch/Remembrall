<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

interface Header {
	/**
	 * Field of the header, for example Content-Type or X-Powered-By
	 * @return string
	 */
	public function field(): string;

	/**
	 * Value of the header, for example text/html; charset=utf-8
	 * @return string
	 */
	public function value(): string;

	/**
	 * Is the given header equals to the current one?
	 * @param Header $header
	 * @return bool
	 */
	public function equals(self $header): bool;
}