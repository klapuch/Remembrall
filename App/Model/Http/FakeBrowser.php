<?php
declare(strict_types = 1);
namespace Remembrall\Model\Http;

final class FakeBrowser implements Browser {
	private $response;

	public function __construct(Response $response = null) {
	    $this->response = $response;
	}

	public function send(Request $request): Response {
		return $this->response;
	}
}