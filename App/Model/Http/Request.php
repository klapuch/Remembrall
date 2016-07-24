<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use Remembrall\Model\Subscribing;

interface Request {
	/**
	 * Send the request
	 * @return Subscribing\Page
	 */
	public function send(): Subscribing\Page;
}