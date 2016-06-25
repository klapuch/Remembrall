<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

interface Request {
	/**
	 * Headers gained from the request
	 * @return Headers
	 */
	public function headers(): Headers;
}