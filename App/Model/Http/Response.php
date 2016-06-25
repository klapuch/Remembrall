<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

interface Response {
	/**
	 * Headers gained from the response
	 * @return Headers
	 */
	public function headers(): Headers;

	/**
	 * Content returned by the host
	 * @return string
	 */
	public function content(): string;
}