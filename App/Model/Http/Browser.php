<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use Remembrall\Exception;
use Remembrall\Model\Subscribing;

interface Browser {
	/**
	 * Send the given request and return page
	 * @param Request $request
	 * @throws Exception\NotFoundException
	 * @return Subscribing\Page
	 */
	public function send(Request $request): Subscribing\Page;
}