<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

interface Browser {
	/**
	 * Send the given request and return response
	 * @param Request $request
	 * @return Response
	 */
	public function send(Request $request): Response;
}