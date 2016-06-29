<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

use Remembrall\Exception;

interface Browser {
	/**
	 * Send the given request and return response
	 * @param Request $request
	 * @throws Exception\ExistenceException
	 * @return Response
	 */
	public function send(Request $request): Response;
}